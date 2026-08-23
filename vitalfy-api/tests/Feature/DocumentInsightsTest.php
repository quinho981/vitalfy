<?php

namespace Tests\Feature;

use App\Jobs\ProcessGenerateInsightsAI;
use App\Models\Document;
use App\Models\DocumentTemplate;
use App\Models\DocumentTemplateCategory;
use App\Models\Transcript;
use App\Models\TranscriptType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Critério de conclusão de BE-R2-05: mesma autorização do stream (401/404/200),
 * mais o estado de pendência (204) quando o job ainda não persistiu insights.
 */
class DocumentInsightsTest extends TestCase
{
    use RefreshDatabase;

    private function createDocumentFor(User $owner): Document
    {
        $transcriptType = TranscriptType::create(['type' => 'Consulta']);
        $category = DocumentTemplateCategory::create(['name' => 'Categoria Teste']);
        $documentTemplate = DocumentTemplate::create([
            'name' => 'Modelo padrão',
            'category_id' => $category->id,
        ]);

        $transcript = Transcript::create([
            'user_id' => $owner->id,
            'transcript_type_id' => $transcriptType->id,
            'patient' => 'Paciente Teste',
            'conversation' => ['speaker' => 'médico', 'text' => 'teste'],
        ]);

        return Document::create([
            'transcript_id' => $transcript->id,
            'document_template_id' => $documentTemplate->id,
            'patient' => 'Paciente Teste',
            'result' => '<p>documento de teste</p>',
        ]);
    }

    public function test_sem_cookie_responde_401(): void
    {
        $owner = User::factory()->create();
        $document = $this->createDocumentFor($owner);

        $response = $this->getJson("/api/documents/{$document->id}/insights");

        $response->assertStatus(401);
    }

    public function test_nao_dono_responde_404(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $document = $this->createDocumentFor($owner);

        Sanctum::actingAs($other);

        $response = $this->getJson("/api/documents/{$document->id}/insights");

        $response->assertStatus(404);
    }

    public function test_documento_inexistente_responde_404_identico_ao_de_nao_dono(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $document = $this->createDocumentFor($owner);

        Sanctum::actingAs($other);
        $naoDono = $this->getJson("/api/documents/{$document->id}/insights");

        Sanctum::actingAs($other);
        $inexistente = $this->getJson('/api/documents/00000000-0000-0000-0000-000000000000/insights');

        $naoDono->assertStatus(404);
        $inexistente->assertStatus(404);
        $this->assertSame($naoDono->json('message'), $inexistente->json('message'));
    }

    public function test_dono_sem_insights_ainda_responde_204(): void
    {
        $owner = User::factory()->create();
        $document = $this->createDocumentFor($owner);

        Sanctum::actingAs($owner);

        $response = $this->get("/api/documents/{$document->id}/insights");

        $response->assertStatus(204);
    }

    public function test_dono_com_insights_prontos_responde_200_com_os_oito_campos(): void
    {
        $owner = User::factory()->create();
        $document = $this->createDocumentFor($owner);

        $document->ai_insights()->create([
            'red_flags' => ['nenhuma'],
            'case_severity' => ['verde'],
            'brief_description' => ['resumo'],
            'possible_diagnoses' => ['diagnóstico'],
            'suggested_cid_codes' => ['A00'],
            'suggested_exams' => ['exame'],
            'suggested_conducts' => ['conduta'],
            'missing_clinical_information' => ['nada'],
        ]);

        Sanctum::actingAs($owner);

        $response = $this->getJson("/api/documents/{$document->id}/insights");

        $response->assertStatus(200);
        $response->assertExactJson([
            'red_flags' => ['nenhuma'],
            'case_severity' => ['verde'],
            'brief_description' => ['resumo'],
            'possible_diagnoses' => ['diagnóstico'],
            'suggested_cid_codes' => ['A00'],
            'suggested_exams' => ['exame'],
            'suggested_conducts' => ['conduta'],
            'missing_clinical_information' => ['nada'],
        ]);
    }

    public function test_dono_com_job_falho_responde_200_com_failed_true(): void
    {
        $owner = User::factory()->create();
        $document = $this->createDocumentFor($owner);
        $document->update(['insights_failed_at' => now()]);

        Sanctum::actingAs($owner);

        $response = $this->getJson("/api/documents/{$document->id}/insights");

        $response->assertStatus(200);
        $response->assertExactJson([
            'failed' => true,
            'failure_reason' => 'Não foi possível gerar os insights automaticamente.',
        ]);
    }

    public function test_regenerate_insights_limpa_o_sinal_de_falha_anterior(): void
    {
        Queue::fake([ProcessGenerateInsightsAI::class]);

        $owner = User::factory()->create();
        $document = $this->createDocumentFor($owner);
        $document->update(['insights_failed_at' => now()]);

        Sanctum::actingAs($owner);

        $this->postJson("/api/documents/{$document->id}/regenerate-insights")
            ->assertStatus(200);

        Queue::assertPushed(ProcessGenerateInsightsAI::class, 1);

        $this->assertNull($document->fresh()->insights_failed_at);

        $response = $this->getJson("/api/documents/{$document->id}/insights");
        $response->assertStatus(204);
    }

    public function test_insights_continuam_disponiveis_apos_expirar_a_janela_de_cache_do_sse(): void
    {
        // O caso que o SSE nunca cobriu: cache expirado (60s), banco continua
        // servindo. Simulado aqui sem sleep real — a rota nem consulta o
        // cache, só o banco, então "expirar" é irrelevante para ela.
        $owner = User::factory()->create();
        $document = $this->createDocumentFor($owner);

        $document->ai_insights()->create([
            'red_flags' => [],
            'case_severity' => [],
            'brief_description' => [],
            'possible_diagnoses' => [],
            'suggested_cid_codes' => [],
            'suggested_exams' => [],
            'suggested_conducts' => [],
            'missing_clinical_information' => [],
        ]);

        Sanctum::actingAs($owner);

        $response = $this->getJson("/api/documents/{$document->id}/insights");

        $response->assertStatus(200);
    }
}
