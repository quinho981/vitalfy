<?php

namespace Tests\Feature;

use App\Enums\TranscriptStatusEnum;
use App\Events\TranscriptCreated;
use App\Models\Document;
use App\Models\DocumentTemplate;
use App\Models\DocumentTemplateCategory;
use App\Models\Transcript;
use App\Models\TranscriptType;
use App\Models\User;
use App\Services\DeepgramService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\InteractsWithAsyncPipelineFlag;
use Tests\TestCase;

/**
 * BE-R19-02 (ai-vitalfy/action-plans/shared/R19.md, SH-R19-01): botão
 * "Transcrever" (POST /transcripts) passa a responder 202 e processar em
 * background quando FEATURE_ASYNC_TRANSCRIPT_PIPELINE está ligada,
 * reaproveitando ProcessGenerateDocumentPipeline em modo "só transcrição"
 * (templateId null) — mesma máquina de R1, sem gerar Document.
 */
class TranscriptStoreAsyncPipelineTest extends TestCase
{
    use RefreshDatabase;
    use InteractsWithAsyncPipelineFlag;

    protected function tearDown(): void
    {
        $this->restoreAsyncTranscriptPipeline();

        parent::tearDown();
    }

    private function createPayload(TranscriptType $type, DocumentTemplate $template): array
    {
        return [
            'audio' => UploadedFile::fake()->create('audio.mp3', 50, 'audio/mpeg'),
            'patient' => 'Paciente Teste',
            'type' => $type->id,
            // A validação de StoreTranscriptRequest exige `template` mesmo
            // para este endpoint, que não o usa (comportamento pré-existente,
            // fora do escopo deste plano) — precisa ser enviado para passar
            // da validação.
            'template' => $template->id,
        ];
    }

    private function fakeUtterances(): array
    {
        return [
            ['speaker' => 0, 'transcript' => 'Olá, tudo bem?', 'start' => 0.0, 'end' => 2.5],
            ['speaker' => 1, 'transcript' => 'Tudo sim, doutor.', 'start' => 2.5, 'end' => 4.2],
        ];
    }

    public function test_flag_ligada_responde_202_processa_em_sync_e_conclui_sem_document(): void
    {
        $this->setAsyncTranscriptPipeline(true);

        // TranscriptCreated dispara ao fim da transcrição (decisão 4 de
        // SH-R19-01) e QUEUE_CONNECTION=sync roda o listener
        // SendFirstTranscriptionEmail de verdade dentro da requisição — bug
        // pré-existente e fora de escopo (R18: espera int, recebe UUID).
        // Fake só o evento, não o comportamento do endpoint em si.
        Event::fake([TranscriptCreated::class]);

        $user = User::factory()->create();
        $type = TranscriptType::create(['type' => 'Consulta']);
        $category = DocumentTemplateCategory::create(['name' => 'Categoria Teste']);
        $template = DocumentTemplate::create([
            'name' => 'Modelo padrão',
            'category_id' => $category->id,
            'content' => 'Contexto: {context}',
        ]);

        $this->mock(DeepgramService::class, function ($mock) {
            $mock->shouldReceive('transcribeAudio')
                ->once()
                ->andReturn($this->fakeUtterances());
        });

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/transcripts', $this->createPayload($type, $template));

        $response->assertStatus(202);
        $response->assertJsonStructure(['transcript_id', 'status']);

        $transcriptId = $response->json('transcript_id');
        $this->assertNotNull($transcriptId);

        // QUEUE_CONNECTION=sync em testing (phpunit.xml): o job já rodou por
        // completo dentro da própria requisição — a resposta reflete o
        // estado no momento da criação (pending), o banco já reflete o
        // resultado final do processamento.
        $transcript = Transcript::find($transcriptId);
        $this->assertNotNull($transcript);
        $this->assertSame(TranscriptStatusEnum::Completed, $transcript->status);
        $this->assertNotNull($transcript->conversation);
        $this->assertNull($transcript->audio_storage_path);
        $this->assertSame(0, Document::query()->where('transcript_id', $transcript->id)->count());

        $status = $this->getJson("/api/transcripts/{$transcript->id}/status");
        $status->assertStatus(200);
        $status->assertJson([
            'status' => 'completed',
            'transcript_id' => $transcript->id,
        ]);
    }

    /**
     * BE-R19-05, item 5: com a flag desligada, o endpoint continua
     * exatamente como antes deste plano — síncrono, 201.
     */
    public function test_flag_desligada_continua_sincrona_e_responde_201(): void
    {
        $this->setAsyncTranscriptPipeline(false);

        // Mesmo motivo do teste acima: neutraliza o bug pré-existente de R18
        // no listener, fora de escopo deste plano.
        Event::fake([TranscriptCreated::class]);

        $user = User::factory()->create();
        $type = TranscriptType::create(['type' => 'Consulta']);
        $category = DocumentTemplateCategory::create(['name' => 'Categoria Teste']);
        $template = DocumentTemplate::create([
            'name' => 'Modelo padrão',
            'category_id' => $category->id,
            'content' => 'Contexto: {context}',
        ]);

        $this->mock(DeepgramService::class, function ($mock) {
            $mock->shouldReceive('transcribeAudio')
                ->once()
                ->andReturn($this->fakeUtterances());
        });

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/transcripts', $this->createPayload($type, $template));

        $response->assertStatus(201);

        // processAudioAndCreate() devolve { transcript: {...}, remaining: N }
        // (formato do caminho síncrono legado, inalterado por este plano).
        $transcriptId = $response->json('transcript.id');
        $this->assertNotNull($transcriptId);

        $transcript = Transcript::find($transcriptId);
        $this->assertSame(TranscriptStatusEnum::Completed, $transcript->status);
        $this->assertSame(0, Document::query()->where('transcript_id', $transcript->id)->count());
    }
}
