<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\DocumentTemplate;
use App\Models\DocumentTemplateCategory;
use App\Models\Transcript;
use App\Models\TranscriptType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * BE-R4-04 (ai-vitalfy/action-plans/backend/R4.md): `DocumentPolicy::view`
 * (usada por `GET /documents/{id}/insights`) já tem 404 uniforme testado por
 * R2. `DocumentPolicy::update` (usada por `PUT /documents/{id}`,
 * `POST /documents/{id}/regenerate-insights` e `GET /documents/{id}/pdf`)
 * devolve `bool` simples → 403 padrão do Laravel para não-dono — o
 * vazamento de existência residual que R2 já registrou como aceito
 * (ai-vitalfy/risks.md#r2), nunca testado até agora. Este teste documenta
 * esse comportamento atual, não o corrige.
 */
class DocumentOwnershipTest extends TestCase
{
    use RefreshDatabase;

    private const INEXISTENTE = '00000000-0000-0000-0000-000000000000';

    private function createDocumentFor(User $owner): Document
    {
        $type = TranscriptType::firstOrCreate(['type' => 'Consulta']);
        $transcript = Transcript::create([
            'user_id' => $owner->id,
            'transcript_type_id' => $type->id,
            'patient' => 'Paciente Teste',
            'conversation' => ['speaker' => 'médico', 'text' => 'teste'],
        ]);

        $category = DocumentTemplateCategory::firstOrCreate(['name' => 'Categoria Teste']);
        $template = DocumentTemplate::create([
            'name' => 'Modelo padrão',
            'category_id' => $category->id,
            'content' => 'Contexto: {context}',
        ]);

        return Document::create([
            'transcript_id' => $transcript->id,
            'document_template_id' => $template->id,
            'patient' => 'Paciente Teste',
            'result' => '<p>documento existente</p>',
        ]);
    }

    public function test_update_responde_403_para_nao_dono_vazamento_de_existencia_residual_aceito_em_r2(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $document = $this->createDocumentFor($owner);

        Sanctum::actingAs($other);
        $this->putJson("/api/documents/{$document->id}", ['result' => '<p>alterado</p>'])
            ->assertStatus(403);

        $this->assertSame('<p>documento existente</p>', $document->fresh()->result);
    }

    public function test_regenerate_insights_responde_403_para_nao_dono(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $document = $this->createDocumentFor($owner);

        Sanctum::actingAs($other);
        $this->postJson("/api/documents/{$document->id}/regenerate-insights")
            ->assertStatus(403);
    }

    public function test_generate_pdf_responde_403_para_nao_dono(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $document = $this->createDocumentFor($owner);

        Sanctum::actingAs($other);
        $this->getJson("/api/documents/{$document->id}/pdf")
            ->assertStatus(403);
    }

    public function test_update_com_documento_inexistente_responde_404(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);
        $this->putJson('/api/documents/' . self::INEXISTENTE, ['result' => '<p>x</p>'])
            ->assertStatus(404);
    }

    public function test_dono_atualiza_documento_normalmente(): void
    {
        $owner = User::factory()->create();
        $document = $this->createDocumentFor($owner);

        Sanctum::actingAs($owner);
        $this->putJson("/api/documents/{$document->id}", ['result' => '<p>alterado pelo dono</p>'])
            ->assertStatus(200);

        $this->assertSame('<p>alterado pelo dono</p>', $document->fresh()->result);
    }
}
