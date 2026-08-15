<?php

namespace Tests\Feature;

use App\Enums\TranscriptStatusEnum;
use App\Events\TranscriptCreated;
use App\Jobs\ProcessGenerateInsightsAI;
use App\Models\Document;
use App\Models\DocumentTemplate;
use App\Models\DocumentTemplateCategory;
use App\Models\Transcript;
use App\Models\TranscriptType;
use App\Models\User;
use App\Services\DocumentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\InteractsWithAsyncPipelineFlag;
use Tests\TestCase;

/**
 * BE-R19-04 (ai-vitalfy/action-plans/shared/R19.md, SH-R19-01):
 * POST /documents/generate passa a responder 202 e reaproveitar
 * ProcessGenerateDocumentPipeline quando FEATURE_ASYNC_TRANSCRIPT_PIPELINE
 * está ligada — sempre depois das guardas de posse/duplicidade que
 * BE-R19-03 já cobre em DocumentGenerateTest (aqui flag sempre ligada).
 */
class DocumentGenerateAsyncPipelineTest extends TestCase
{
    use RefreshDatabase;
    use InteractsWithAsyncPipelineFlag;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setAsyncTranscriptPipeline(true);
    }

    protected function tearDown(): void
    {
        $this->restoreAsyncTranscriptPipeline();

        parent::tearDown();
    }

    private function createTranscriptFor(User $owner, ?array $conversation = ['speaker' => 'médico', 'text' => 'teste']): Transcript
    {
        $transcriptType = TranscriptType::create(['type' => 'Consulta']);

        return Transcript::create([
            'user_id' => $owner->id,
            'transcript_type_id' => $transcriptType->id,
            'patient' => 'Paciente Teste',
            'conversation' => $conversation,
            'status' => $conversation === null ? TranscriptStatusEnum::Pending : TranscriptStatusEnum::Completed,
        ]);
    }

    private function createDocumentTemplate(): DocumentTemplate
    {
        $category = DocumentTemplateCategory::create(['name' => 'Categoria Teste']);

        return DocumentTemplate::create([
            'name' => 'Modelo padrão',
            'category_id' => $category->id,
            'content' => 'Contexto: {context}',
        ]);
    }

    private function payload(Transcript $transcript, DocumentTemplate $template): array
    {
        return [
            'transcript_id' => $transcript->id,
            'patient' => 'Paciente Teste',
            'template' => $template->id,
        ];
    }

    public function test_flag_ligada_responde_202_e_job_cria_document_e_dispara_insights(): void
    {
        // ProcessGenerateInsightsAI é a única etapa cara depois da criação do
        // Document — faked para não bater no Groq de verdade. O job pai
        // (ProcessGenerateDocumentPipeline) roda de verdade (QUEUE_CONNECTION
        // =sync em testing), então a fila só precisa ser fakeada para esta
        // classe específica (Queue::fake aceita subconjunto).
        Queue::fake([ProcessGenerateInsightsAI::class]);

        // TranscriptCreated também dispara aqui (decisão 4 de SH-R19-01),
        // e QUEUE_CONNECTION=sync roda o listener SendFirstTranscriptionEmail
        // de verdade dentro da requisição — que tem um bug pré-existente e
        // fora de escopo (R18: espera int, recebe UUID). Fake só o evento,
        // não o listener em si, para não mascarar nada do que este teste
        // realmente cobre (Document + insights).
        Event::fake([TranscriptCreated::class]);

        $owner = User::factory()->create();
        $transcript = $this->createTranscriptFor($owner);
        $template = $this->createDocumentTemplate();

        $this->mock(DocumentService::class, function ($mock) {
            $mock->shouldReceive('generateLlmDocument')
                ->once()
                ->andReturn('<p>documento gerado</p>');
        });

        Sanctum::actingAs($owner);
        $response = $this->postJson('/api/documents/generate', $this->payload($transcript, $template));

        $response->assertStatus(202);
        $response->assertJson([
            'transcript_id' => $transcript->id,
        ]);
        $response->assertJsonStructure(['transcript_id', 'status']);

        $document = Document::query()->where('transcript_id', $transcript->id)->first();
        $this->assertNotNull($document, 'o job síncrono já deveria ter criado o Document dentro da própria requisição de teste');
        $this->assertSame('<p>documento gerado</p>', $document->result);

        $this->assertSame(TranscriptStatusEnum::Completed, $transcript->fresh()->status);

        Queue::assertPushed(ProcessGenerateInsightsAI::class, 1);
    }

    /**
     * BE-R19-05, item 3: duas requisições para a mesma transcrição — a
     * segunda encontra o lock já tomado (simulado tomando o lock manualmente
     * antes, mesmo padrão de TranscriptConcurrencyLockTest para o lock de
     * R1) e recebe 409, sem criar Document nem chamar o Groq.
     */
    public function test_lock_por_transcricao_a_segunda_chamada_concorrente_responde_409(): void
    {
        $owner = User::factory()->create();
        $transcript = $this->createTranscriptFor($owner);
        $template = $this->createDocumentTemplate();

        $lock = Cache::lock("transcript-document-generation:{$transcript->id}", 200);
        $this->assertTrue($lock->get(), 'pré-condição: o teste precisa conseguir tomar o lock primeiro');

        $this->mock(DocumentService::class, function ($mock) {
            $mock->shouldNotReceive('generateLlmDocument');
        });

        Sanctum::actingAs($owner);
        $response = $this->postJson('/api/documents/generate', $this->payload($transcript, $template));

        $response->assertStatus(409);
        $response->assertJson(['message' => 'Geração de documento já em andamento para esta transcrição.']);

        $this->assertSame(0, Document::query()->where('transcript_id', $transcript->id)->count());
    }

    /**
     * BE-R19-05, item 4: transcrição sem conversation (transcrição ainda não
     * concluída) não deveria chegar via UI normal, mas a API precisa se
     * proteger mesmo assim.
     */
    public function test_transcricao_sem_conversation_responde_422(): void
    {
        $owner = User::factory()->create();
        $transcript = $this->createTranscriptFor($owner, conversation: null);
        $template = $this->createDocumentTemplate();

        $this->mock(DocumentService::class, function ($mock) {
            $mock->shouldNotReceive('generateLlmDocument');
        });

        Sanctum::actingAs($owner);
        $response = $this->postJson('/api/documents/generate', $this->payload($transcript, $template));

        $response->assertStatus(422);

        $this->assertSame(0, Document::query()->where('transcript_id', $transcript->id)->count());
    }
}
