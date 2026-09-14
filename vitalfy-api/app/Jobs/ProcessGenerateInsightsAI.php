<?php

namespace App\Jobs;

use App\Exceptions\InvalidMedicalAnalysisException;
use App\Models\Document;
use App\Services\DocumentService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessGenerateInsightsAI implements ShouldQueue
{
    use Queueable;

    protected string $documentId;
    protected array $conversation;

    /**
     * O teto de vazão do Groq é por minuto e compartilhado entre todas as
     * chamadas da organização (ai-vitalfy/CAPACITY.md): a extração factual e
     * estes insights do MESMO documento disputam a mesma janela, e o 429
     * chega justamente porque as duas acontecem com segundos de diferença.
     *
     * Com `tries = 1` — o default do supervisor em config/horizon.php — um
     * único 429 perdia os insights daquele documento em definitivo, com
     * insights_failed_at preenchido e nenhuma nova tentativa. O documento
     * clínico saía, os insights não, e nada além do log dizia por quê.
     *
     * Os intervalos são escolhidos contra a janela de 60s do TPM: a
     * primeira retentativa cai fora da janela que estourou, a segunda cobre
     * o caso de a fila ainda estar carregada.
     */
    public int $tries = 3;

    public function backoff(): array
    {
        return [20, 45];
    }

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
            try {
                $insights = $documentService->generateInsightsAI($this->conversation);
            } catch (InvalidMedicalAnalysisException $e) {
                // Contrato violado pelo modelo não é falha transitória:
                // retentar gasta a mesma quantidade de token para receber o
                // mesmo payload inválido. Falha na hora, sem consumir as
                // tentativas reservadas para rate limit e indisponibilidade.
                $this->fail($e);
                return;
            }

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

    public function failed(?Throwable $exception): void
    {
        $document = Document::find($this->documentId);

        if (! $document || $document->ai_insights) {
            return;
        }

        Log::error('insights.pipeline.failed', [
            'document_id' => $this->documentId,
            'exception' => $exception?->getMessage(),
        ]);

        $document->update(['insights_failed_at' => now()]);
    }
}
