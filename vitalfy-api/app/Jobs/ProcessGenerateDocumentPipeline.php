<?php

namespace App\Jobs;

use App\Enums\TranscriptStatusEnum;
use App\Events\TranscriptCreated;
use App\Mail\TranscriptLimitWarningMail;
use App\Models\Transcript;
use App\Services\DeepgramService;
use App\Services\DocumentService;
use App\Services\TranscriptService;
use App\Support\PlanLimits;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Throwable;

/**
 * BE-R1-06/07/08 (ai-vitalfy/action-plans/backend/R1.md): substitui a
 * execução síncrona de Deepgram+Groq dentro do request de
 * POST /transcripts/generate-document. Contrato congelado em
 * ai-vitalfy/action-plans/R1.md (SH-R1-01).
 *
 * Idempotência (BE-R1-08): cada etapa cara é persistida antes de a próxima
 * começar e checada no início de handle() — uma retentativa nunca refaz uma
 * etapa que já produziu resultado, então nunca paga Deepgram/Groq duas vezes
 * pelo mesmo trabalho.
 *
 * BE-R19-02/04 (ai-vitalfy/action-plans/shared/R19.md, SH-R19-01): também
 * processa os dois fluxos novos que reaproveitam este mesmo job —
 * `POST /transcripts` assíncrono (só transcrição, $templateId null) e
 * `POST /documents/generate` assíncrono (documento a partir de transcrição
 * já concluída). $templateId null pula inteiramente a etapa de documento;
 * a etapa de transcrição e o restante do fluxo (evento, log, aviso de
 * limite) são idênticos nos dois modos.
 */
class ProcessGenerateDocumentPipeline implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public array $backoff = [15, 60, 180];

    /**
     * Folgado para áudio de até 30 min (AudioLimits::MAX_RECORDING_DURATION_SECONDS).
     * Sem isso o worker aplicaria o limite padrão de 60s.
     */
    public int $timeout = 900;

    /**
     * Precisa ser MAIOR que $timeout. Se o retry_after do driver de fila
     * (redis, default 90s — config/queue.php) for menor que o tempo real de
     * execução, o job é considerado "perdido" e reenfileirado enquanto ainda
     * roda — duas execuções concorrentes do mesmo pipeline, pagando
     * Deepgram/Groq em dobro. A idempotência acima amortece o dano; este
     * valor evita a causa.
     */
    public int $retryAfter = 960;

    /**
     * BE-R19-02/04 (ai-vitalfy/action-plans/shared/R19.md, SH-R19-01):
     * templateId agora é opcional — null significa "só transcrição", modo
     * usado pelo endpoint `POST /transcripts` assíncrono, que não gera
     * Document. Preenchido, o comportamento é idêntico ao pipeline original
     * de R1 (transcrição + geração de documento).
     */
    public function __construct(
        public readonly string $transcriptId,
        public readonly ?int $templateId = null,
    ) {
    }

    public function handle(DeepgramService $deepgramService, DocumentService $documentService, TranscriptService $transcriptService): void
    {
        $transcript = Transcript::find($this->transcriptId);

        if (! $transcript || $transcript->status === TranscriptStatusEnum::Completed) {
            // Excluída pelo usuário enquanto esperava na fila, ou retry
            // tardio de um job que já concluiu — não há o que fazer.
            return;
        }

        $requestStart = microtime(true);
        $deepgramMs = null;
        $groqMs = null;

        try {
            if ($transcript->conversation === null) {
                $transcript->status = TranscriptStatusEnum::Transcribing;
                $transcript->save();

                $deepgramStart = microtime(true);
                [$conversation, $endTime] = $this->transcribeStoredAudio($transcript, $deepgramService, $transcriptService);
                $deepgramMs = (int) ((microtime(true) - $deepgramStart) * 1000);

                $transcript->conversation = $conversation;
                $transcript->end_conversation_time = $endTime;
                $transcript->save();

                // A partir daqui uma retentativa não precisa mais do áudio
                // bruto — ver política de retenção em BE-R1-08.
                $this->deleteStoredAudio($transcript);
            }

            $document = null;

            if ($this->templateId !== null) {
                if (! $transcript->document) {
                    $transcript->status = TranscriptStatusEnum::Generating;
                    $transcript->save();

                    $groqStart = microtime(true);
                    $documentContent = $documentService->generateLlmDocument(
                        $transcript->conversation,
                        $this->templateId,
                        $transcript->id
                    );
                    $groqMs = (int) ((microtime(true) - $groqStart) * 1000);

                    $document = $transcript->document()->create([
                        'document_template_id' => $this->templateId,
                        'patient' => $transcript->patient,
                        'result' => $documentContent,
                    ]);
                } else {
                    $document = $transcript->document;
                }
            }

            $transcript->status = TranscriptStatusEnum::Completed;
            $transcript->save();

            $transcriptService->logPipelineDuration(
                $transcript->id,
                $transcript->file_size ?? 0,
                $deepgramMs,
                $groqMs,
                $requestStart,
                async: true,
            );

            if ($document) {
                ProcessGenerateInsightsAI::dispatch($document->id, $transcript->conversation);
            }

            TranscriptCreated::dispatch($transcript, $transcript->user);

            $this->dispatchLimitWarningIfNeeded($transcript);
        } catch (InvalidArgumentException $e) {
            // Áudio excede a duração máxima ou arquivo sumiu do disco —
            // falha definitiva, não adianta retentar. $this->fail() marca o
            // job como falho imediatamente, sem consumir as tries restantes.
            $this->markFailed($transcript, $e->getMessage());
            $this->fail($e);
        }
    }

    public function failed(?Throwable $exception): void
    {
        $transcript = Transcript::find($this->transcriptId);

        if (! $transcript || $transcript->status === TranscriptStatusEnum::Completed) {
            return;
        }

        $this->markFailed(
            $transcript,
            'Não foi possível concluir o processamento. Tente enviar o áudio novamente.'
        );
    }

    private function markFailed(Transcript $transcript, string $reason): void
    {
        $transcript->status = TranscriptStatusEnum::Failed;
        $transcript->failure_reason = $reason;
        $transcript->save();

        $this->deleteStoredAudio($transcript);
    }

    /**
     * @return array{0: array, 1: int} [conversation, end_conversation_time]
     */
    private function transcribeStoredAudio(Transcript $transcript, DeepgramService $deepgramService, TranscriptService $transcriptService): array
    {
        $path = $transcript->audio_storage_path;
        $disk = Storage::disk('local');

        if (! $path || ! $disk->exists($path)) {
            throw new InvalidArgumentException('Arquivo de áudio não encontrado para processamento.');
        }

        $content = $disk->get($path);
        $mimeType = $disk->mimeType($path);

        $utterances = $deepgramService->transcribeAudio($content, $mimeType);

        $transcriptService->validateAudioDuration($utterances);

        $conversation = $transcriptService->organizeUtterances($utterances);
        $endTime = $transcriptService->getLastEndUtteranceTime($utterances);

        return [$conversation, $endTime];
    }

    private function deleteStoredAudio(Transcript $transcript): void
    {
        if ($transcript->audio_storage_path) {
            Storage::disk('local')->delete($transcript->audio_storage_path);
            $transcript->audio_storage_path = null;
            $transcript->save();
        }
    }

    private function dispatchLimitWarningIfNeeded(Transcript $transcript): void
    {
        $user = $transcript->user;

        if ($user->hasProPlan()) {
            return;
        }

        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();
        $used = Transcript::fromUserBetweenDates($user->id, $startOfMonth, $endOfMonth)->completed()->count();
        $remaining = PlanLimits::FREE_MONTHLY_TRANSCRIPTS - $used;

        if ($remaining === 2) {
            Mail::to($user->email)->queue(
                new TranscriptLimitWarningMail($user->name, config('app.frontend_url'))
            );
        }
    }
}
