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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class ProcessGenerateInsightsAIFailureTest extends TestCase
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

    public function test_failed_grava_insights_failed_at_e_loga_erro_estruturado(): void
    {
        Log::spy();

        $document = $this->createDocument();
        $job = new ProcessGenerateInsightsAI($document->id, [['speaker' => 'médico', 'text' => 'teste']]);

        $exception = new InvalidMedicalAnalysisException('case_severity fora do enum esperado: "inventado"');
        $job->failed($exception);

        $document->refresh();
        $this->assertNotNull($document->insights_failed_at);
        $this->assertNull($document->ai_insights);

        Log::shouldHaveReceived('error')
            ->once()
            ->with('insights.pipeline.failed', [
                'document_id' => $document->id,
                'exception' => $exception->getMessage(),
            ]);
    }

    public function test_failed_nao_sobrescreve_um_sucesso_ja_persistido(): void
    {
        $document = $this->createDocument();
        $document->ai_insights()->create([
            'red_flags' => [],
            'case_severity' => ['verde'],
            'brief_description' => ['paciente estável'],
            'possible_diagnoses' => [],
            'suggested_cid_codes' => [],
            'suggested_exams' => [],
            'suggested_conducts' => [],
            'missing_clinical_information' => [],
        ]);

        $job = new ProcessGenerateInsightsAI($document->id, [['speaker' => 'médico', 'text' => 'teste']]);
        $job->failed(new InvalidMedicalAnalysisException('falha tardia de uma tentativa concorrente'));

        $this->assertNull($document->fresh()->insights_failed_at);
    }

    public function test_failed_para_documento_inexistente_nao_lanca_erro(): void
    {
        $job = new ProcessGenerateInsightsAI('00000000-0000-0000-0000-000000000000', []);

        $job->failed(new InvalidMedicalAnalysisException('irrelevante'));

        $this->assertTrue(true);
    }
}
