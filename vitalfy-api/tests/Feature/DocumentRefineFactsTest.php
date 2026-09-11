<?php

namespace Tests\Feature;

use App\Enums\TranscriptStatusEnum;
use App\Models\Document;
use App\Models\DocumentTemplate;
use App\Models\DocumentTemplateCategory;
use App\Models\Subscription;
use App\Models\Transcript;
use App\Models\TranscriptType;
use App\Models\User;
use App\Services\DocumentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * BE-R23-08 (ai-vitalfy/action-plans/backend/R23.md): document_id no
 * payload de POST /documents/refine localiza os fatos validados e autoriza
 * por posse.
 *
 * Nota sobre o critério de conclusão do plano: o texto do plano descreve
 * "o mesmo 404 que DocumentPolicy já produz para não-dono", mas
 * DocumentPolicy::update() (usado aqui, não view()) devolve 403 para
 * não-dono — o próprio comentário da policy documenta isso como
 * "vazamento de existência residual conhecido e aceito por ora", fora do
 * escopo de R2. Este teste verifica o comportamento real (403), não o
 * texto do plano.
 */
class DocumentRefineFactsTest extends TestCase
{
    use RefreshDatabase;

    private function giveActiveSubscription(User $user): void
    {
        Subscription::create([
            'user_id' => $user->id,
            'type' => 'default',
            'stripe_id' => 'sub_' . uniqid(),
            'stripe_status' => 'active',
            'ends_at' => null,
        ]);
    }

    private function createDocumentFor(User $owner, ?array $clinicalFacts = null): Document
    {
        $type = TranscriptType::create(['type' => 'Consulta']);
        $category = DocumentTemplateCategory::create(['name' => 'Categoria Teste']);
        $template = DocumentTemplate::create([
            'name' => 'Modelo Teste',
            'category_id' => $category->id,
            'content' => 'Contexto: {context}',
        ]);

        $transcript = Transcript::create([
            'user_id' => $owner->id,
            'transcript_type_id' => $type->id,
            'patient' => 'Paciente Teste',
            'conversation' => [['speaker' => 1, 'text' => 'teste']],
            'clinical_facts' => $clinicalFacts,
            'status' => TranscriptStatusEnum::Completed,
        ]);

        return Document::create([
            'transcript_id' => $transcript->id,
            'document_template_id' => $template->id,
            'patient' => 'Paciente Teste',
            'result' => '<p>documento original</p>',
        ]);
    }

    public function test_sem_document_id_comporta_se_como_antes_desta_tarefa(): void
    {
        $user = User::factory()->create();
        $this->giveActiveSubscription($user);

        $this->mock(DocumentService::class, function ($mock) {
            $mock->shouldReceive('refineDocument')
                ->once()
                ->withArgs(fn (array $data) => !array_key_exists('clinical_facts', $data))
                ->andReturn('texto refinado');
        });

        Sanctum::actingAs($user);
        $response = $this->postJson('/api/documents/refine', ['conversation' => '<p>documento</p>']);

        $response->assertStatus(200);
        $response->assertJson(['content' => 'texto refinado']);
    }

    public function test_com_document_id_do_proprio_usuario_carrega_clinical_facts_no_payload(): void
    {
        $user = User::factory()->create();
        $this->giveActiveSubscription($user);

        $facts = ['schema_version' => 'clinical-facts/1', 'template_id' => 1, 'sections' => []];
        $document = $this->createDocumentFor($user, $facts);

        $this->mock(DocumentService::class, function ($mock) use ($facts) {
            $mock->shouldReceive('refineDocument')
                ->once()
                ->withArgs(fn (array $data) => ($data['clinical_facts'] ?? null) == $facts)
                ->andReturn('texto refinado');
        });

        Sanctum::actingAs($user);
        $response = $this->postJson('/api/documents/refine', [
            'conversation' => '<p>documento</p>',
            'document_id' => $document->id,
        ]);

        $response->assertStatus(200);
    }

    public function test_com_document_id_de_outro_usuario_responde_403(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $this->giveActiveSubscription($other);

        $document = $this->createDocumentFor($owner);

        $this->mock(DocumentService::class, fn ($mock) => $mock->shouldNotReceive('refineDocument'));

        Sanctum::actingAs($other);
        $response = $this->postJson('/api/documents/refine', [
            'conversation' => '<p>documento</p>',
            'document_id' => $document->id,
        ]);

        $response->assertStatus(403);
    }

    public function test_com_document_id_inexistente_responde_404(): void
    {
        $user = User::factory()->create();
        $this->giveActiveSubscription($user);

        $this->mock(DocumentService::class, fn ($mock) => $mock->shouldNotReceive('refineDocument'));

        Sanctum::actingAs($user);
        $response = $this->postJson('/api/documents/refine', [
            'conversation' => '<p>documento</p>',
            'document_id' => (string) \Illuminate\Support\Str::uuid(),
        ]);

        $response->assertStatus(404);
    }

    public function test_document_id_de_transcricao_sem_clinical_facts_envia_null(): void
    {
        $user = User::factory()->create();
        $this->giveActiveSubscription($user);

        $document = $this->createDocumentFor($user, null);

        $this->mock(DocumentService::class, function ($mock) {
            $mock->shouldReceive('refineDocument')
                ->once()
                ->withArgs(fn (array $data) => array_key_exists('clinical_facts', $data) && $data['clinical_facts'] === null)
                ->andReturn('texto refinado');
        });

        Sanctum::actingAs($user);
        $response = $this->postJson('/api/documents/refine', [
            'conversation' => '<p>documento</p>',
            'document_id' => $document->id,
        ]);

        $response->assertStatus(200);
    }
}
