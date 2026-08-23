<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessGenerateInsightsAI;
use App\Models\Document;
use App\Models\Transcript;
use App\Policies\DocumentPolicy;
use App\Services\DocumentService;
use App\Services\TranscriptService;
use App\Support\FeatureFlags;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Spatie\LaravelPdf\Facades\Pdf;

class DocumentController extends Controller
{
    protected DocumentService $documentService;
    protected TranscriptService $transcriptService;

    public function __construct(DocumentService $documentService, TranscriptService $transcriptService)
    {
        $this->documentService = $documentService;
        $this->transcriptService = $transcriptService;
    }

    public function update(Document $document, Request $request): Document
    {
        $this->authorize('update', $document);

        $data = $request->all();

        // BE-R10-04 (ai-vitalfy/risks.md#r10): segundo caminho que grava
        // `result` — edição manual ("Salvar") e aceite de refinamento
        // ("Salvar Refinamento") — mesma sanitização de BE-R10-03. Só a
        // chave `result` é tocada; o resto do payload segue intacto.
        if (array_key_exists('result', $data)) {
            $data['result'] = $this->documentService->sanitizeClinicalHtml($data['result']);
        }

        $document->update($data);

        return $document;
    }

    /**
     * BE-R19 (ai-vitalfy/risks.md): esta era a única ação da classe sem
     * authorize() — o payload manda transcript_id livre e o Document era
     * criado para qualquer transcrição, inclusive de outro usuário.
     *
     * Busca manual em vez de route model binding implícito, pelo mesmo
     * motivo documentado em insights() acima: o binding implícito lançaria
     * ModelNotFoundException com mensagem diferente da que a policy usa,
     * reabrindo o oráculo de existência por texto.
     */
    public function generate(Request $request): JsonResponse
    {
        $transcript = Transcript::find($request->input('transcript_id'));

        if (! $transcript) {
            abort(404, DocumentPolicy::NOT_FOUND_MESSAGE);
        }

        $this->authorize('generateDocument', $transcript);

        // Duplo clique / retry não deve gerar um segundo Document nem pagar
        // o Groq de novo — checagem de posse (authorize acima) sempre antes
        // desta, para não vazar "documento já existe" a quem não é dono.
        if ($transcript->document) {
            return response()->json([
                'message' => 'Documento já gerado para esta transcrição.',
            ], 409);
        }

        // BE-R19-04 (ai-vitalfy/action-plans/shared/R19.md, SH-R19-01):
        // reaproveita o mesmo pipeline assíncrono de R1/BE-R19-02 — a flag
        // decide em runtime, sem deploy. enqueueDocumentGeneration() tem seu
        // próprio lock por transcrição, para a janela de corrida entre duas
        // requisições simultâneas que passam pelas duas checagens acima
        // antes de qualquer uma delas criar o Document.
        if (FeatureFlags::asyncTranscriptPipeline()) {
            $result = $this->transcriptService->enqueueDocumentGeneration(
                $transcript,
                (int) $request->input('template')
            );

            return response()->json($result, 202);
        }

        $document = $this->documentService->createDocumentAndDispatchInsights($request->all());

        return response()->json($document, 201);
    }

    public function regenerateInsights(Document $document): JsonResponse
    {
        $this->authorize('update', $document);

        $conversation = $document->transcript->conversation;

        $document->update(['insights_failed_at' => null]);

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
            if ($documentModel->insights_failed_at) {
                return response()->json([
                    'failed' => true,
                    'failure_reason' => 'Não foi possível gerar os insights automaticamente.',
                ]);
            }

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
