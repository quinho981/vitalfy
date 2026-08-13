<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\DocumentTemplate;
use App\Models\DocumentTemplateCategory;
use App\Models\Transcript;
use App\Models\TranscriptType;
use App\Models\User;
use App\Services\DocumentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\InteractsWithAsyncPipelineFlag;
use Tests\TestCase;

/**
 * R19 (ai-vitalfy/risks.md): DocumentController::generate() era a única ação
 * da classe sem authorize() — o payload manda transcript_id livre, então
 * qualquer usuário autenticado podia gerar um Document para a transcrição de
 * outra pessoa. Cobre também o guard de duplicidade (dobro clique não deve
 * criar um segundo Document nem pagar o Groq duas vezes).
 *
 * BE-R19-05 (ai-vitalfy/action-plans/shared/R19.md): estes testes descrevem
 * o comportamento síncrono do endpoint (o que ele fazia antes de
 * BE-R19-04 aprender a devolver 202 sob a flag) — flag forçada desligada
 * para a classe inteira, deliberadamente, então esta classe também serve
 * como a regressão "com a flag desligada, o endpoint se comporta exatamente
 * como antes" pedida em BE-R19-05. O caminho assíncrono (flag ligada) é
 * coberto à parte em DocumentGenerateAsyncPipelineTest.
 */
class DocumentGenerateTest extends TestCase
{
    use RefreshDatabase;
    use InteractsWithAsyncPipelineFlag;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setAsyncTranscriptPipeline(false);
    }

    protected function tearDown(): void
    {
        $this->restoreAsyncTranscriptPipeline();

        parent::tearDown();
    }

    private function createTranscriptFor(User $owner): Transcript
    {
        $transcriptType = TranscriptType::create(['type' => 'Consulta']);

        return Transcript::create([
            'user_id' => $owner->id,
            'transcript_type_id' => $transcriptType->id,
            'patient' => 'Paciente Teste',
            'conversation' => ['speaker' => 'médico', 'text' => 'teste'],
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

    private function generatePayload(Transcript $transcript, DocumentTemplate $template): array
    {
        return [
            'transcript_id' => $transcript->id,
            'patient' => 'Paciente Teste',
            'template' => $template->id,
            'conversation' => [
                ['text' => 'médico: teste'],
            ],
        ];
    }

    public function test_transcript_de_outro_usuario_responde_404_identico_ao_de_transcript_inexistente(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $transcript = $this->createTranscriptFor($owner);
        $template = $this->createDocumentTemplate();

        // O serviço não deve ser chamado quando o dono não confere: nem o
        // Groq nem a criação do Document podem acontecer nesse caminho.
        $this->mock(DocumentService::class, function ($mock) {
            $mock->shouldNotReceive('createDocumentAndDispatchInsights');
        });

        Sanctum::actingAs($other);
        $naoDono = $this->postJson('/api/documents/generate', $this->generatePayload($transcript, $template));

        Sanctum::actingAs($other);
        $inexistente = $this->postJson('/api/documents/generate', $this->generatePayload(
            new Transcript(['id' => '00000000-0000-0000-0000-000000000000']),
            $template
        ));

        $naoDono->assertStatus(404);
        $inexistente->assertStatus(404);
        $this->assertSame($naoDono->json('message'), $inexistente->json('message'));

        $this->assertSame(0, Document::count());
    }

    public function test_segunda_chamada_para_transcricao_ja_documentada_responde_409_sem_criar_segundo_documento(): void
    {
        $owner = User::factory()->create();
        $transcript = $this->createTranscriptFor($owner);
        $template = $this->createDocumentTemplate();

        Document::create([
            'transcript_id' => $transcript->id,
            'document_template_id' => $template->id,
            'patient' => 'Paciente Teste',
            'result' => '<p>documento já existente</p>',
        ]);

        // Duplicidade precisa ser barrada antes de qualquer chamada ao Groq.
        $this->mock(DocumentService::class, function ($mock) {
            $mock->shouldNotReceive('createDocumentAndDispatchInsights');
        });

        Sanctum::actingAs($owner);
        $response = $this->postJson('/api/documents/generate', $this->generatePayload($transcript, $template));

        $response->assertStatus(409);
        $response->assertJson(['message' => 'Documento já gerado para esta transcrição.']);

        $this->assertSame(1, Document::query()->where('transcript_id', $transcript->id)->count());
    }

    public function test_dono_sem_documento_previo_responde_201_comportamento_inalterado(): void
    {
        $owner = User::factory()->create();
        $transcript = $this->createTranscriptFor($owner);
        $template = $this->createDocumentTemplate();

        $fakeDocument = new Document([
            'transcript_id' => $transcript->id,
            'document_template_id' => $template->id,
            'patient' => 'Paciente Teste',
            'result' => '<p>documento gerado</p>',
        ]);
        $fakeDocument->id = '11111111-1111-1111-1111-111111111111';

        $this->mock(DocumentService::class, function ($mock) use ($fakeDocument) {
            $mock->shouldReceive('createDocumentAndDispatchInsights')
                ->once()
                ->andReturn($fakeDocument);
        });

        Sanctum::actingAs($owner);
        $response = $this->postJson('/api/documents/generate', $this->generatePayload($transcript, $template));

        $response->assertStatus(201);
        $response->assertJson([
            'id' => $fakeDocument->id,
            'transcript_id' => $transcript->id,
            'document_template_id' => $template->id,
            'result' => '<p>documento gerado</p>',
        ]);
    }
}
