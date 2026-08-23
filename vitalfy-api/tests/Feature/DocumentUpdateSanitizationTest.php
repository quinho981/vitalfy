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
 * BE-R10-04 (ai-vitalfy/risks.md#r10): segundo caminho de escrita em
 * `documents.result` — usado por "Salvar" (Tiptap) e "Salvar Refinamento" —
 * recebe a mesma sanitização de BE-R10-03.
 */
class DocumentUpdateSanitizationTest extends TestCase
{
    use RefreshDatabase;

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

    public function test_payload_com_script_e_persistido_sanitizado(): void
    {
        $owner = User::factory()->create();
        $document = $this->createDocumentFor($owner);

        Sanctum::actingAs($owner);
        $this->putJson("/api/documents/{$document->id}", [
            'result' => '<script>alert(1)</script><p>texto legítimo</p>',
        ])->assertStatus(200);

        $fresh = $document->fresh()->result;
        $this->assertStringNotContainsString('<script', $fresh);
        $this->assertStringContainsString('<p>texto legítimo</p>', $fresh);
    }

    public function test_payload_com_onerror_e_persistido_sanitizado(): void
    {
        $owner = User::factory()->create();
        $document = $this->createDocumentFor($owner);

        Sanctum::actingAs($owner);
        $this->putJson("/api/documents/{$document->id}", [
            'result' => '<img src=x onerror=alert(2)><p>ok</p>',
        ])->assertStatus(200);

        $fresh = $document->fresh()->result;
        $this->assertStringNotContainsString('onerror', $fresh);
        $this->assertStringContainsString('<p>ok</p>', $fresh);
    }

    public function test_payload_sem_a_chave_result_continua_funcionando_sem_regressao(): void
    {
        $owner = User::factory()->create();
        $document = $this->createDocumentFor($owner);

        Sanctum::actingAs($owner);
        $this->putJson("/api/documents/{$document->id}", [
            'feedback' => 'ótimo atendimento',
        ])->assertStatus(200);

        $fresh = $document->fresh();
        $this->assertSame('<p>documento existente</p>', $fresh->result);
        $this->assertSame('ótimo atendimento', $fresh->feedback);
    }
}
