<?php

namespace Tests\Feature;

use App\Exceptions\InvalidMedicalAnalysisException;
use App\Jobs\ProcessGenerateInsightsAI;
use App\Models\Document;
use App\Models\DocumentTemplate;
use App\Models\DocumentTemplateCategory;
use App\Models\Transcript;
use App\Models\TranscriptType;
use App\Models\User;
use App\Services\DocumentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * BE-R7-03 (ai-vitalfy/action-plans/backend/R7.md): uma resposta de
 * generateInsightsAI() fora do contrato esperado (chave ausente,
 * case_severity fora do enum ACCR) nao pode virar uma linha em ai_insights.
 * ProcessGenerateInsightsAI::handle() acessava as chaves direto, sem
 * verificar nada.
 *
 * ATUALIZADO em 13/09/2026: a garantia de BE-R7-03 — payload fora do
 * contrato nao vira linha em ai_insights — segue intacta e continua sendo o
 * que estes testes provam. O que mudou foi o MECANISMO da falha. Desde que
 * o job ganhou `tries = 3` para sobreviver a rate limit, deixar a
 * InvalidMedicalAnalysisException propagar faria as tres tentativas
 * acontecerem: ~8.700 tokens gastos para receber tres vezes o mesmo payload
 * invalido, ocupando a janela de TPM de que o proximo documento precisa.
 * O job agora chama $this->fail() e nao retenta. A excecao deixa de escapar
 * de handle(), e por isso os dois primeiros testes nao a esperam mais.
 */
class ProcessGenerateInsightsAIValidationTest extends TestCase
{
    use RefreshDatabase;

    private function createDocument(): Document
    {
        $owner = User::factory()->create();
        $transcriptType = TranscriptType::create(['type' => 'Consulta']);

        $transcript = Transcript::create([
            'user_id' => $owner->id,
            'transcript_type_id' => $transcriptType->id,
            'patient' => 'Paciente Teste',
            'conversation' => [['speaker' => 'médico', 'text' => 'teste']],
        ]);

        $category = DocumentTemplateCategory::create(['name' => 'Categoria Teste']);
        $template = DocumentTemplate::create([
            'name' => 'Modelo padrão',
            'category_id' => $category->id,
            'content' => 'Contexto: {context}',
        ]);

        return Document::create([
            'document_template_id' => $template->id,
            'patient' => 'Paciente Teste',
            'result' => '<p>documento gerado</p>',
            'transcript_id' => $transcript->id,
        ]);
    }

    public function test_case_severity_fora_do_enum_nao_persiste_e_falha_sem_retentar(): void
    {
        $document = $this->createDocument();

        $this->mock(DocumentService::class, function ($mock) {
            $mock->shouldReceive('generateInsightsAI')
                ->once()
                ->andThrow(new InvalidMedicalAnalysisException(
                    'case_severity fora do enum esperado: "inventado"'
                ));
        });

        $job = new ProcessGenerateInsightsAI($document->id, [['speaker' => 'médico', 'text' => 'teste']]);

        $job->handle(app(DocumentService::class));

        $this->assertDatabaseCount('ai_insights', 0);
        $this->assertNull($document->transcript->fresh()->description);
    }

    public function test_chave_ausente_nao_persiste_e_falha_sem_retentar(): void
    {
        $document = $this->createDocument();

        $this->mock(DocumentService::class, function ($mock) {
            $mock->shouldReceive('generateInsightsAI')
                ->once()
                ->andThrow(new InvalidMedicalAnalysisException('Campo "red_flags" ausente ou não é um array.'));
        });

        $job = new ProcessGenerateInsightsAI($document->id, [['speaker' => 'médico', 'text' => 'teste']]);

        $job->handle(app(DocumentService::class));

        $this->assertDatabaseCount('ai_insights', 0);
    }

    public function test_resposta_valida_continua_persistindo_normalmente(): void
    {
        $document = $this->createDocument();

        $medicalAnalysis = [
            'red_flags' => [],
            'case_severity' => ['verde'],
            'brief_description' => ['paciente estável'],
            'possible_diagnoses' => [],
            'suggested_cid_codes' => [],
            'suggested_exams' => [],
            'suggested_conducts' => [],
            'missing_clinical_information' => [],
        ];

        $this->mock(DocumentService::class, function ($mock) use ($medicalAnalysis) {
            $mock->shouldReceive('generateInsightsAI')
                ->once()
                ->andReturn(['medical_analysis' => $medicalAnalysis]);
        });

        $job = new ProcessGenerateInsightsAI($document->id, [['speaker' => 'médico', 'text' => 'teste']]);
        $job->handle(app(DocumentService::class));

        $this->assertDatabaseCount('ai_insights', 1);
        $this->assertSame('paciente estável', $document->transcript->fresh()->description);
    }
}
