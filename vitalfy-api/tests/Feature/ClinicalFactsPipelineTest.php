<?php

namespace Tests\Feature;

use App\Enums\TranscriptStatusEnum;
use App\Events\TranscriptCreated;
use App\Jobs\ProcessGenerateDocumentPipeline;
use App\Jobs\ProcessGenerateInsightsAI;
use App\Models\Document;
use App\Models\DocumentTemplate;
use App\Models\DocumentTemplateCategory;
use App\Models\Transcript;
use App\Models\TranscriptType;
use App\Models\User;
use App\Services\ClinicalFactsExtractor;
use App\Services\DocumentService;
use App\Support\ClinicalDocumentRenderer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Tests\Concerns\InteractsWithClinicalFactsExtractionFlag;
use Tests\TestCase;

/**
 * BE-R23-06/BE-R23-09 (ai-vitalfy/action-plans/backend/R23.md): os sete
 * cenários — flag desligada, caminho feliz, fallback de extração, fallback
 * de montagem, idempotência com mesmo template, reextração com outro
 * template, modo só-transcrição. ClinicalFactsExtractor é o único ponto
 * mockado (é o que toca o Groq); ClinicalFactsValidator e
 * ClinicalDocumentRenderer rodam de verdade — são lógica pura, sem rede.
 */
class ClinicalFactsPipelineTest extends TestCase
{
    use RefreshDatabase;
    use InteractsWithClinicalFactsExtractionFlag;

    protected function tearDown(): void
    {
        $this->restoreClinicalFactsExtraction();

        parent::tearDown();
    }

    private function createUserAndTranscript(): Transcript
    {
        $user = User::factory()->create();
        $type = TranscriptType::create(['type' => 'Consulta']);

        return Transcript::create([
            'user_id' => $user->id,
            'transcript_type_id' => $type->id,
            'patient' => 'Paciente Teste',
            'conversation' => [
                ['speaker' => 1, 'text' => 'Paciente relata dor no peito há três dias.'],
                ['speaker' => 0, 'text' => 'Vou pedir um exame.'],
            ],
            'status' => TranscriptStatusEnum::Pending,
        ]);
    }

    private function createTemplate(): DocumentTemplate
    {
        $category = DocumentTemplateCategory::create(['name' => 'Categoria Teste']);

        return DocumentTemplate::create([
            'name' => 'Modelo com seções',
            'category_id' => $category->id,
            'content' => 'Contexto: {context}',
            'sections' => [
                ['key' => 'queixa_principal', 'label' => 'Queixa Principal', 'render' => 'prose'],
                [
                    'key' => 'exames_relevantes',
                    'label' => 'Exames Relevantes',
                    'render' => 'prose',
                    'status_enum' => ['realizado', 'solicitado', 'mencionado_sem_especificacao'],
                ],
            ],
        ]);
    }

    private function validFactsPayload(int $templateId): array
    {
        return [
            'schema_version' => 'clinical-facts/1',
            'template_id' => $templateId,
            'title' => ['text' => 'Dor torácica', 'source_key' => 'queixa_principal'],
            'sections' => [
                [
                    'key' => 'queixa_principal',
                    'items' => [[
                        'text' => 'Paciente refere dor torácica há três dias.',
                        'status' => 'relatado',
                        'speaker' => 1,
                        'evidence' => 'Paciente relata dor no peito há três dias',
                    ]],
                ],
                [
                    'key' => 'exames_relevantes',
                    'items' => [[
                        'text' => null,
                        'status' => 'mencionado_sem_especificacao',
                        'speaker' => 0,
                        'evidence' => 'Vou pedir um exame',
                    ]],
                ],
            ],
        ];
    }

    public function test_flag_desligada_comporta_se_como_hoje_e_clinical_facts_fica_null(): void
    {
        $this->setClinicalFactsExtraction(false);
        Queue::fake([ProcessGenerateInsightsAI::class]);
        Event::fake([TranscriptCreated::class]);

        $transcript = $this->createUserAndTranscript();
        $template = $this->createTemplate();

        $this->mock(ClinicalFactsExtractor::class, fn ($mock) => $mock->shouldNotReceive('extract'));
        $this->mock(DocumentService::class, function ($mock) {
            $mock->shouldReceive('generateLlmDocument')->once()->andReturn('<p>documento legado</p>');
        });

        ProcessGenerateDocumentPipeline::dispatch($transcript->id, $template->id);

        $document = Document::where('transcript_id', $transcript->id)->first();

        $this->assertNotNull($document);
        $this->assertSame('<p>documento legado</p>', $document->result);
        $this->assertNull($document->facts_fallback_at);
        $this->assertNull($transcript->fresh()->clinical_facts);
    }

    public function test_caminho_feliz_persiste_clinical_facts_e_monta_documento_sem_llm_de_geracao(): void
    {
        $this->setClinicalFactsExtraction(true);
        Queue::fake([ProcessGenerateInsightsAI::class]);
        Event::fake([TranscriptCreated::class]);

        $transcript = $this->createUserAndTranscript();
        $template = $this->createTemplate();

        $this->mock(ClinicalFactsExtractor::class, function ($mock) use ($template) {
            $mock->shouldReceive('extract')->once()->andReturn($this->validFactsPayload($template->id));
        });
        $this->partialMock(DocumentService::class, function ($mock) {
            $mock->shouldNotReceive('generateLlmDocument');
        });

        ProcessGenerateDocumentPipeline::dispatch($transcript->id, $template->id);

        $document = Document::where('transcript_id', $transcript->id)->first();
        $transcript->refresh();

        $this->assertNotNull($document);
        $this->assertNull($document->facts_fallback_at);
        $this->assertStringContainsString('<h2><strong>Dor torácica</strong></h2>', $document->result);
        $this->assertStringContainsString('Paciente refere dor torácica há três dias.', $document->result);
        $this->assertStringContainsString('Mencionada a necessidade de Exames Relevantes', $document->result);

        $this->assertNotNull($transcript->clinical_facts);
        $this->assertSame($template->id, $transcript->clinical_facts['template_id']);
    }

    public function test_extrator_falhando_cai_no_fallback_e_marca_facts_fallback_at(): void
    {
        $this->setClinicalFactsExtraction(true);
        Queue::fake([ProcessGenerateInsightsAI::class]);
        Event::fake([TranscriptCreated::class]);

        $transcript = $this->createUserAndTranscript();
        $template = $this->createTemplate();

        $this->mock(ClinicalFactsExtractor::class, function ($mock) {
            $mock->shouldReceive('extract')->once()->andThrow(new \App\Exceptions\ClinicalFactsExtractionException('Groq indisponível'));
        });
        $this->partialMock(DocumentService::class, function ($mock) {
            $mock->shouldReceive('generateLlmDocument')->once()->andReturn('<p>documento legado via fallback</p>');
        });

        ProcessGenerateDocumentPipeline::dispatch($transcript->id, $template->id);

        $document = Document::where('transcript_id', $transcript->id)->first();

        $this->assertNotNull($document);
        $this->assertSame('<p>documento legado via fallback</p>', $document->result);
        $this->assertNotNull($document->facts_fallback_at);
        $this->assertNull($transcript->fresh()->clinical_facts);
    }

    public function test_montador_falhando_cai_no_fallback_mas_clinical_facts_ja_persistido_permanece(): void
    {
        $this->setClinicalFactsExtraction(true);
        Queue::fake([ProcessGenerateInsightsAI::class]);
        Event::fake([TranscriptCreated::class]);

        $transcript = $this->createUserAndTranscript();
        $template = $this->createTemplate();

        $this->mock(ClinicalFactsExtractor::class, function ($mock) use ($template) {
            $mock->shouldReceive('extract')->once()->andReturn($this->validFactsPayload($template->id));
        });
        $this->mock(ClinicalDocumentRenderer::class, function ($mock) {
            $mock->shouldReceive('render')->once()->andThrow(new \RuntimeException('falha de montagem'));
        });
        $this->partialMock(DocumentService::class, function ($mock) {
            $mock->shouldReceive('generateLlmDocument')->once()->andReturn('<p>documento legado via fallback</p>');
        });

        ProcessGenerateDocumentPipeline::dispatch($transcript->id, $template->id);

        $document = Document::where('transcript_id', $transcript->id)->first();
        $transcript->refresh();

        $this->assertNotNull($document);
        $this->assertSame('<p>documento legado via fallback</p>', $document->result);
        $this->assertNotNull($document->facts_fallback_at);
        // A extração e validação aconteceram e persistiram antes da
        // montagem falhar -- não é desfeita pelo fallback.
        $this->assertNotNull($transcript->clinical_facts);
    }

    public function test_reexecutar_com_clinical_facts_do_mesmo_template_nao_chama_o_extrator(): void
    {
        $this->setClinicalFactsExtraction(true);
        Queue::fake([ProcessGenerateInsightsAI::class]);
        Event::fake([TranscriptCreated::class]);

        $transcript = $this->createUserAndTranscript();
        $template = $this->createTemplate();

        // Simula um job anterior que já extraiu e persistiu os fatos, mas
        // morreu antes de criar o Document -- retentativa deve pular a
        // extração e só remontar.
        $transcript->clinical_facts = $this->validFactsPayload($template->id);
        $transcript->save();

        $this->mock(ClinicalFactsExtractor::class, fn ($mock) => $mock->shouldNotReceive('extract'));
        $this->partialMock(DocumentService::class, fn ($mock) => $mock->shouldNotReceive('generateLlmDocument'));

        ProcessGenerateDocumentPipeline::dispatch($transcript->id, $template->id);

        $document = Document::where('transcript_id', $transcript->id)->first();

        $this->assertNotNull($document);
        $this->assertNull($document->facts_fallback_at);
        $this->assertStringContainsString('Paciente refere dor torácica há três dias.', $document->result);
    }

    public function test_reexecutar_com_outro_template_chama_o_extrator_de_novo(): void
    {
        $this->setClinicalFactsExtraction(true);
        Queue::fake([ProcessGenerateInsightsAI::class]);
        Event::fake([TranscriptCreated::class]);

        $transcript = $this->createUserAndTranscript();
        $originalTemplate = $this->createTemplate();
        $otherTemplate = $this->createTemplate();

        $transcript->clinical_facts = $this->validFactsPayload($originalTemplate->id);
        $transcript->save();

        $this->mock(ClinicalFactsExtractor::class, function ($mock) use ($otherTemplate) {
            $mock->shouldReceive('extract')->once()->andReturn($this->validFactsPayload($otherTemplate->id));
        });

        ProcessGenerateDocumentPipeline::dispatch($transcript->id, $otherTemplate->id);

        $document = Document::where('transcript_id', $transcript->id)->first();
        $transcript->refresh();

        $this->assertNotNull($document);
        $this->assertSame($otherTemplate->id, $transcript->clinical_facts['template_id']);
    }

    public function test_modo_so_transcricao_nunca_chama_o_extrator(): void
    {
        $this->setClinicalFactsExtraction(true);
        Queue::fake([ProcessGenerateInsightsAI::class]);
        Event::fake([TranscriptCreated::class]);

        $transcript = $this->createUserAndTranscript();

        $this->mock(ClinicalFactsExtractor::class, fn ($mock) => $mock->shouldNotReceive('extract'));

        ProcessGenerateDocumentPipeline::dispatch($transcript->id, null);

        $this->assertSame(0, Document::where('transcript_id', $transcript->id)->count());
        $this->assertNull($transcript->fresh()->clinical_facts);
    }
}
