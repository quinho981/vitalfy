<?php

namespace App\Services;

use App\Enums\TranscriptStatusEnum;
use App\Events\TranscriptCreated;
use App\Http\Requests\StoreTranscriptRequest;
use App\Jobs\ProcessGenerateDocumentPipeline;
use App\Jobs\ProcessGenerateInsightsAI;
use App\Mail\TranscriptLimitWarningMail;
use App\Models\Transcript;
use App\Support\AudioLimits;
use App\Support\PlanLimits;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;

class TranscriptService
{
    protected Transcript $transcript;
    protected DeepgramService $deepgramService;
    protected DocumentService $documentService;

    public function __construct(
        Transcript $transcript,
        DeepgramService $deepgramService,
        DocumentService $documentService
    )
    {
        $this->transcript = $transcript;
        $this->deepgramService = $deepgramService;
        $this->documentService = $documentService;
    }

    public function getUserTranscripts(string $userId): LengthAwarePaginator
    {
        return $this->baseTranscriptHistoryQuery()
            ->where('user_id', $userId)
            ->paginate(10);
    }

    public function searchUserTranscripts(array $request, string $userId): Collection
    {
        $username = $request['user'] ?? null;
        $date = $request['date'] ?? null;
        $type = $request['type'] ?? null;

        $query = $this->baseTranscriptHistoryQuery()
            ->where('user_id', $userId);

        if($username) {
            $query->where('patient', 'ILIKE', "%{$username}%");
        }

        if($date) {
            $date = Carbon::parse($request['date'])->toDateString();

            $query->whereDate('created_at', $date);
        }

        if ($type) {
            $query->where('transcript_type_id', $type);
        }

        return $query->limit(30)->get();
    }

    private function baseTranscriptHistoryQuery(): Builder
    {
        return $this->transcript
            ->with([
                'document:id,transcript_id,document_template_id',
                'document.documentTemplate:id,name,category_id',
                'document.documentTemplate.category:id,color',
                'transcriptType:id,type',
            ])
            ->select(['id', 'transcript_type_id', 'patient', 'end_conversation_time', 'file_size', 'description', 'status', 'failure_reason', 'created_at'])
            ->selectRaw('LEFT(description, 86) as description')
            ->latest();
    }

    public function getTranscriptAndDocument(string $id): object
    {
        return $this->transcript
            ->with([
                'document:id,transcript_id,document_template_id,result,created_at,feedback',
                'document.documentTemplate:id,name',
                'document.ai_insights:id,document_id,possible_diagnoses,red_flags,case_severity,brief_description,possible_diagnoses,suggested_cid_codes,suggested_exams,suggested_conducts,missing_clinical_information'
            ])
            ->where('id', $id)
            ->firstOrFail(['id', 'patient', 'created_at', 'end_conversation_time']);
    }

    public function deleteTranscript(string $id): void
    {
        $transcript = $this->transcript->findOrFail($id);
        $transcript->delete();
    }

    public function getConversations(string $id): object
    {
        $transcript = $this->transcript
            ->where('id', $id)
            ->first(['id', 'conversation']);

        return $transcript;
    }

    /**
     * Estado do processamento para o front (BE-R1-07). 404 para não-dono e
     * inexistente é decidido pela policy, não aqui — ver
     * TranscriptPolicy::viewStatus.
     */
    public function getStatus(string $id): array
    {
        $transcript = $this->transcript
            ->where('id', $id)
            ->firstOrFail(['id', 'status', 'failure_reason']);

        return [
            'id' => $transcript->id,
            'status' => $transcript->status->value,
            // Redireciona pelo id da transcrição — a rota de detalhe já
            // resolve o documento associado (ver upload.vue#redirectTo).
            'transcript_id' => $transcript->status === TranscriptStatusEnum::Completed ? $transcript->id : null,
            'failure_reason' => $transcript->failure_reason,
            // Falha de validação (áudio grande demais) é definitiva; qualquer
            // outra (erro de API externa) pode ser tentada de novo pelo
            // usuário — ver contrato em ai-vitalfy/action-plans/R1.md.
            'recoverable' => $transcript->status === TranscriptStatusEnum::Failed
                ? ! str_starts_with((string) $transcript->failure_reason, 'O áudio excede')
                : null,
        ];
    }

    private function processAudioAndBuildConversation(StoreTranscriptRequest $request): array
    {
        $file = $request->file('audio');
        $audio = $this->getAudioContent($file);

        $deepgramStart = microtime(true);
        $utterances = $this->deepgramService->transcribeAudio($audio['content'], $audio['mimeType']);
        $deepgramMs = (int) ((microtime(true) - $deepgramStart) * 1000);

        $conversation = $this->organizeUtterances($utterances);

        return [
            'file' => $file,
            'utterances' => $utterances,
            'conversation' => $conversation,
            'deepgram_ms' => $deepgramMs,
        ];
    }

    public function processAudioAndCreate(StoreTranscriptRequest $request): array
    {
        $user = $request->user();
        $remainingTranscripts = null;
        $requestStart = microtime(true);

        [
            'file' => $file,
            'utterances' => $utterances,
            'conversation' => $conversation,
            'deepgram_ms' => $deepgramMs,
        ] = $this->processAudioAndBuildConversation($request);

        $this->validateAudioDuration($utterances);

        $transcript = Transcript::create([
            'user_id' => $user->id,
            'patient' => $request['patient'],
            'conversation' => $conversation,
            'transcript_type_id' => $request['type'],
            'end_conversation_time' => $this->getLastEndUtteranceTime($utterances),
            'file_size' => $file->getSize(),
            'status' => TranscriptStatusEnum::Completed,
        ]);

        if(!$user->hasProPlan()) {
            $remainingTranscripts = $this->getRemainingMonthlyTranscripts($user->id);
            $this->dispatchLimitWarningIfNeeded($user, $remainingTranscripts);
        }

        TranscriptCreated::dispatch($transcript, $user);

        $this->logPipelineDuration($transcript->id, $file->getSize(), $deepgramMs, null, $requestStart, async: false);

        return [
            'transcript' => $transcript,
            'remaining' => $remainingTranscripts
        ];
    }

    /**
     * Caminho síncrono legado (D3, revisado em BE-R1-06). Mantido atrás da
     * feature flag de SH-R1-02 como caminho de rollback sem deploy — ver
     * TranscriptController::storeAndGenerateDocument. Não recebe mais
     * mudanças estruturais; a evolução do pipeline acontece em
     * enqueueGenerateDocument().
     */
    public function storeAndGenerateDocument(StoreTranscriptRequest $request): array
    {
        $user = $request->user();
        $remainingTranscripts = null;
        $requestStart = microtime(true);

        [
            'file' => $file,
            'utterances' => $utterances,
            'conversation' => $conversation,
            'deepgram_ms' => $deepgramMs,
        ] = $this->processAudioAndBuildConversation($request);

        $this->validateAudioDuration($utterances);

        $groqStart = microtime(true);
        $documentContent = $this->documentService->generateLlmDocument($conversation, $request['template']);
        $groqMs = (int) ((microtime(true) - $groqStart) * 1000);

        $document = DB::transaction(function () use ($request, $file, $utterances, $conversation, $documentContent) {
            $transcript = Transcript::create([
                'user_id' => Auth::id(),
                'patient' => $request['patient'],
                'conversation' => $conversation,
                'transcript_type_id' => $request['type'],
                'end_conversation_time' => $this->getLastEndUtteranceTime($utterances),
                'file_size' => $file->getSize(),
                'status' => TranscriptStatusEnum::Completed,
            ]);

            $document = $transcript->document()->create([
                'document_template_id' => $request['template'],
                'patient' => $request['patient'],
                'result' => $documentContent,
                'transcript_id' => $request['transcript_id']
            ]);

            return $document;
        });

        if(!$user->hasProPlan()) {
            $remainingTranscripts = $this->getRemainingMonthlyTranscripts($user->id);
            $this->dispatchLimitWarningIfNeeded($user, $remainingTranscripts);
        }

        ProcessGenerateInsightsAI::dispatch($document->id, $conversation);
        TranscriptCreated::dispatch($document->transcript, $user);

        $this->logPipelineDuration($document->transcript_id, $file->getSize(), $deepgramMs, $groqMs, $requestStart, async: false);

        return [
            'document' => $document,
            'remaining' => $remainingTranscripts
        ];
    }

    /**
     * Caminho assíncrono (BE-R1-06). Responde rápido: cria a linha em
     * `pending`, persiste o áudio em disco e enfileira o pipeline. Contrato
     * completo em ai-vitalfy/action-plans/R1.md (SH-R1-01).
     *
     * Cota: debitada na conclusão (status completed), não aqui — ver
     * Transcript::scopeCompleted() e CheckTranscriptLimit. Por isso este
     * método não calcula `remaining`: o valor exibido ao criar não mudaria
     * (o processamento ainda não terminou).
     */
    public function enqueueGenerateDocument(StoreTranscriptRequest $request): array
    {
        $transcript = $this->createPendingTranscriptAndStoreAudio($request);

        ProcessGenerateDocumentPipeline::dispatch($transcript->id, (int) $request['template']);

        return [
            'transcript_id' => $transcript->id,
            'status' => $transcript->status->value,
        ];
    }

    /**
     * BE-R19-02 (ai-vitalfy/action-plans/shared/R19.md, SH-R19-01): caminho
     * assíncrono do botão "Transcrever" (sem documento). Idêntico a
     * enqueueGenerateDocument() exceto por não ter templateId — o job roda
     * em modo "só transcrição" (ver ProcessGenerateDocumentPipeline::handle).
     */
    public function enqueueTranscription(StoreTranscriptRequest $request): array
    {
        $transcript = $this->createPendingTranscriptAndStoreAudio($request);

        ProcessGenerateDocumentPipeline::dispatch($transcript->id, null);

        return [
            'transcript_id' => $transcript->id,
            'status' => $transcript->status->value,
        ];
    }

    /**
     * BE-R19-04 (ai-vitalfy/action-plans/shared/R19.md, SH-R19-01): caminho
     * assíncrono de `POST /documents/generate` — enfileira o mesmo pipeline
     * de R1/R19-02 para gerar o documento a partir de uma transcrição já
     * concluída (chamado depois das guardas de posse/duplicidade de
     * BE-R19-03 em DocumentController::generate()).
     *
     * Lock por transcrição, não por usuário (diferente de
     * PreventConcurrentTranscription, que é por usuário): cobre a janela de
     * corrida entre duas requisições simultâneas para a mesma transcrição —
     * o 409 do controller (BE-R19-03) cobre "documento já existe", este
     * cobre "duas requisições colidindo antes de qualquer uma delas ter
     * criado o documento". TTL igual ao de
     * PreventConcurrentTranscription::LOCK_SECONDS (200s) — mesma rede de
     * segurança para o caso de o `finally` não rodar (worker morto, OOM).
     */
    public function enqueueDocumentGeneration(Transcript $transcript, int $templateId): array
    {
        if ($transcript->conversation === null) {
            abort(422, 'Transcrição ainda não foi concluída.');
        }

        $lock = Cache::lock("transcript-document-generation:{$transcript->id}", 200);

        if (! $lock->get()) {
            abort(409, 'Geração de documento já em andamento para esta transcrição.');
        }

        try {
            // Reconsulta direta ao banco, ignorando qualquer relação já
            // carregada em memória (ex.: a checagem feita pelo controller
            // antes de adquirir o lock) — é exatamente essa janela de
            // corrida que este lock existe para fechar.
            if ($transcript->document()->exists()) {
                abort(409, 'Documento já gerado para esta transcrição.');
            }

            $transcript->status = TranscriptStatusEnum::Generating;
            $transcript->save();

            ProcessGenerateDocumentPipeline::dispatch($transcript->id, $templateId);

            return [
                'transcript_id' => $transcript->id,
                'status' => $transcript->status->value,
            ];
        } finally {
            $lock->release();
        }
    }

    /**
     * BE-R19-02 (ai-vitalfy/action-plans/shared/R19.md): parte comum entre
     * enqueueGenerateDocument() e enqueueTranscription() — cria a transcrição
     * em `pending` e persiste o áudio em disco. A única diferença real entre
     * os dois fluxos é o templateId passado ao job, então só o construtor
     * dela e o retorno de cada método chamador variam.
     */
    private function createPendingTranscriptAndStoreAudio(StoreTranscriptRequest $request): Transcript
    {
        $user = $request->user();
        $file = $request->file('audio');

        $transcript = Transcript::create([
            'user_id' => $user->id,
            'patient' => $request['patient'],
            'conversation' => null,
            'transcript_type_id' => $request['type'],
            'file_size' => $file->getSize(),
            'status' => TranscriptStatusEnum::Pending,
        ]);

        $storagePath = $this->storeUploadedAudio($transcript->id, $file);
        $transcript->update(['audio_storage_path' => $storagePath]);

        return $transcript;
    }

    private function storeUploadedAudio(string $transcriptId, UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension() ?: $file->extension() ?: 'bin';

        return Storage::disk('local')->putFileAs(
            'transcript-uploads',
            $file,
            "{$transcriptId}." . Str::lower($extension)
        );
    }

    private function dispatchLimitWarningIfNeeded($user, int $remaining): void
    {
        if ($remaining === 2) {
            Mail::to($user->email)->queue(
                new TranscriptLimitWarningMail($user->name, config('app.frontend_url'))
            );
        }
    }

    public function getRemainingMonthlyTranscripts(string $userId): int
    {
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        // ->completed(): ver nota de cota em enqueueGenerateDocument().
        $usedTranscripts = Transcript::fromUserBetweenDates($userId, $startOfMonth, $endOfMonth)->completed()->count();

        return PlanLimits::FREE_MONTHLY_TRANSCRIPTS - $usedTranscripts;
    }

    private function getAudioContent(UploadedFile $file): array
    {
        $mimeType = $file->getMimeType();
        $content = file_get_contents($file->getRealPath());

        return ['content' => $content, 'mimeType' => $mimeType];
    }

    public function organizeUtterances(array $utterances): array
    {
        $conversation = [];

        foreach ($utterances as $utterance) {
            $conversation[] = [
                'speaker' => $utterance['speaker'],
                'text' => $utterance['transcript'],
                'start' => round($utterance['start'], 2),
                'end' => round($utterance['end'], 2)
            ];
        }

        return $conversation;
    }

    public function getLastEndUtteranceTime(array $utterances): int
    {
        if (empty($utterances)) return 0;

        $lastUtterance = end($utterances);
        return floor($lastUtterance['end']);
    }

    /**
     * @throws InvalidArgumentException Falha definitiva (BE-R1-07): áudio
     * acima do limite não deve ser retentado.
     */
    public function validateAudioDuration(array $utterances): void
    {
        $duration = $this->getLastEndUtteranceTime($utterances);

        if ($duration > AudioLimits::MAX_RECORDING_DURATION_SECONDS) {
            throw new InvalidArgumentException(
                sprintf(
                    'O áudio excede o limite máximo de %d minutos. Duração atual: %d segundos.',
                    AudioLimits::MAX_RECORDING_DURATION_SECONDS / 60,
                    $duration
                )
            );
        }
    }

    /**
     * BE-R1-02: log estruturado por etapa, sem dado clínico nem conteúdo da
     * conversa — só o necessário para caracterizar a distribuição real de
     * duração (ver ai-vitalfy/action-plans/backend/R1.md#be-r1-02).
     */
    public function logPipelineDuration(
        string $transcriptId,
        int $audioBytes,
        ?int $deepgramMs,
        ?int $groqMs,
        float $requestStart,
        bool $async
    ): void {
        Log::info('transcript.pipeline', [
            'transcript_id' => $transcriptId,
            'audio_bytes' => $audioBytes,
            'deepgram_ms' => $deepgramMs,
            'groq_ms' => $groqMs,
            'total_ms' => (int) ((microtime(true) - $requestStart) * 1000),
            'async' => $async,
        ]);
    }
}
