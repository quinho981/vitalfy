<?php

namespace App\Jobs;

use App\Models\Document;
use App\Services\DocumentService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ProcessGenerateInsightsAI implements ShouldQueue
{
    use Queueable;

    protected string $documentId;
    protected array $conversation;

    /**
     * Create a new job instance.
     */
    public function __construct(string $documentId, array $conversation)
    {
        $this->documentId = $documentId;
        $this->conversation = $conversation;
    }

    /**
     * Execute the job.
     */
    public function handle(DocumentService $documentService)
    {
        $document = Document::find($this->documentId);

        if($document) {
            $insights = $documentService->generateInsightsAI($this->conversation);
            $medicalAnalysis = $insights['medical_analysis'];

            // Persistência direta em ai_insights (fonte de verdade). Até
            // SH-R2-02, isto também publicava em cache (Cache::put) para o
            // stream SSE ler — removido: GET /documents/{document}/insights
            // (BE-R2-05) lê daqui direto, sem depender de janela de cache.
            $document->ai_insights()->create([
                'red_flags' => $medicalAnalysis['red_flags'],
                'case_severity' => $medicalAnalysis['case_severity'],
                'brief_description' => $medicalAnalysis['brief_description'],
                'possible_diagnoses' => $medicalAnalysis['possible_diagnoses'],
                'suggested_cid_codes' => $medicalAnalysis['suggested_cid_codes'],
                'suggested_exams' => $medicalAnalysis['suggested_exams'],
                'suggested_conducts' => $medicalAnalysis['suggested_conducts'],
                'missing_clinical_information' => $medicalAnalysis['missing_clinical_information']
            ]);

            $document->transcript()->update([
                'description' => $medicalAnalysis['brief_description'][0] ?? null
            ]);
        }
    }
}
