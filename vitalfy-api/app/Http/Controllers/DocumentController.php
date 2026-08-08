<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessGenerateInsightsAI;
use App\Models\Document;
use App\Policies\DocumentPolicy;
use App\Services\DocumentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Spatie\LaravelPdf\Facades\Pdf;

class DocumentController extends Controller
{
    protected DocumentService $documentService;

    public function __construct(DocumentService $documentService)
    {
        $this->documentService = $documentService;
    }

    public function update(Document $document, Request $request): Document
    {
        $this->authorize('update', $document);

        $data = $request->all();
        $document->update($data);

        return $document;
    }

    public function generate(Request $request): JsonResponse
    {
        $document = $this->documentService->createDocumentAndDispatchInsights($request->all());

        return response()->json($document, 201);
    }

    public function regenerateInsights(Document $document): JsonResponse
    {
        $this->authorize('update', $document);

        $conversation = $document->transcript->conversation;

        ProcessGenerateInsightsAI::dispatch($document->id, $conversation);

        return response()->json([
            'message' => 'Insights regeneration started'
        ], 200);
    }

    /**
     * Leitura direta dos insights, sem SSE (BE-R2-05). Lê de `ai_insights`
     * (persistido por ProcessGenerateInsightsAI antes do cache), eliminando a
     * dependência da janela de 60s do cache que o stream tinha.
     *
     * Busca manual em vez de route model binding implícito: o binding
     * implícito lançaria ModelNotFoundException com mensagem diferente da que
     * DocumentPolicy::view usa para não-dono — os dois 404 ficariam
     * distinguíveis por texto, reabrindo o oráculo de existência (mesmo
     * motivo documentado em InsightsStreamController::stream).
     */
    public function insights(string $document): JsonResponse|Response
    {
        $documentModel = Document::find($document);

        if (! $documentModel) {
            abort(404, DocumentPolicy::NOT_FOUND_MESSAGE);
        }

        $this->authorize('view', $documentModel);

        $insights = $documentModel->ai_insights;

        // 204: ainda processando (job não concluiu ou nunca foi disparado).
        // O front distingue esse estado do "pronto" para saber quando parar
        // de perguntar (ver FE-R2-03).
        if (! $insights) {
            return response()->noContent();
        }

        // Projeção explícita dos 8 campos — não ->toArray() cru, que vazaria
        // id/document_id/timestamps/deleted_at (metadados do Eloquent que não
        // existem no payload que o SSE emitia hoje).
        return response()->json([
            'red_flags' => $insights->red_flags,
            'case_severity' => $insights->case_severity,
            'brief_description' => $insights->brief_description,
            'possible_diagnoses' => $insights->possible_diagnoses,
            'suggested_cid_codes' => $insights->suggested_cid_codes,
            'suggested_exams' => $insights->suggested_exams,
            'suggested_conducts' => $insights->suggested_conducts,
            'missing_clinical_information' => $insights->missing_clinical_information,
        ]);
    }

    public function refine(Request $request): JsonResponse
    {
        $refined = $this->documentService->refineDocument($request->all());

        return response()->json([
            'content' => $refined
        ], 200);
    }
     
    public function generatePdf(Document $document)
    {
        $this->authorize('update', $document);

        return Pdf::view('pdf.clinical_document', [
            'content' => $document->result,
            'patient_name' => $document->transcript->user->name,
            'template_name' => $document->documentTemplate->name,
            'created_at' => \Carbon\Carbon::parse($document->created_at)->format('d/m/Y H:i'),
        ])
        ->format('A4')
        ->name("document_{$document->id}.pdf")
        ->withBrowsershot(function ($browsershot) {
            $browsershot->newHeadless();
            // php-fpm runs as www-data, whose HOME (/var/www) is not writable;
            // Chrome's crashpad needs a writable HOME to create its database.
            $browsershot->setNodeEnv(['HOME' => '/tmp']);
            $browsershot->addChromiumArguments([
                'disable-dev-shm-usage',
                'disable-gpu',
            ]);
        })
        ->download();
    }
}
