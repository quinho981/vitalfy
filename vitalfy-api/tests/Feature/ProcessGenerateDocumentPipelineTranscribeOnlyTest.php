<?php

namespace Tests\Feature;

use App\Enums\TranscriptStatusEnum;
use App\Events\TranscriptCreated;
use App\Jobs\ProcessGenerateDocumentPipeline;
use App\Jobs\ProcessGenerateInsightsAI;
use App\Models\Document;
use App\Models\Transcript;
use App\Models\TranscriptType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * BE-R19-02 (ai-vitalfy/action-plans/shared/R19.md, SH-R19-01): modo
 * "só transcrição" de ProcessGenerateDocumentPipeline ($templateId null) —
 * usado por TranscriptService::enqueueTranscription(). Não cria Document,
 * não dispara ProcessGenerateInsightsAI, mas ainda dispara TranscriptCreated
 * (decisão 4 de SH-R19-01: mesma semântica do caminho síncrono
 * processAudioAndCreate(), que dispara esse evento independente de haver
 * documento) e termina em Completed.
 *
 * A transcrição já é criada com `conversation` preenchida — pula
 * inteiramente a etapa de transcrição (Deepgram), então não precisa mockar
 * DeepgramService para isolar só o comportamento que este teste cobre.
 */
class ProcessGenerateDocumentPipelineTranscribeOnlyTest extends TestCase
{
    use RefreshDatabase;

    public function test_templateId_nulo_nao_cria_document_nem_dispara_insights_e_termina_completed(): void
    {
        Queue::fake([ProcessGenerateInsightsAI::class]);
        Event::fake([TranscriptCreated::class]);

        $user = User::factory()->create();
        $type = TranscriptType::create(['type' => 'Consulta']);

        $transcript = Transcript::create([
            'user_id' => $user->id,
            'transcript_type_id' => $type->id,
            'patient' => 'Paciente Teste',
            'conversation' => ['speaker' => 'médico', 'text' => 'teste'],
            'file_size' => 12345,
            'status' => TranscriptStatusEnum::Pending,
        ]);

        ProcessGenerateDocumentPipeline::dispatch($transcript->id, null);

        $transcript->refresh();

        $this->assertSame(TranscriptStatusEnum::Completed, $transcript->status);
        $this->assertSame(0, Document::query()->where('transcript_id', $transcript->id)->count());

        Queue::assertNotPushed(ProcessGenerateInsightsAI::class);
        Event::assertDispatched(TranscriptCreated::class);
    }
}
