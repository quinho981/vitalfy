<?php

namespace Tests\Feature;

use App\Enums\TranscriptStatusEnum;
use App\Events\TranscriptCreated;
use App\Exceptions\ClinicalFactsExtractionException;
use App\Jobs\ProcessGenerateInsightsAI;
use App\Models\Document;
use App\Models\DocumentTemplate;
use App\Models\DocumentTemplateCategory;
use App\Models\Transcript;
use App\Models\TranscriptType;
use App\Models\User;
use App\Services\ClinicalFactsExtractor;
use App\Services\DocumentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\InteractsWithAsyncPipelineFlag;
use Tests\Concerns\InteractsWithClinicalFactsExtractionFlag;
use Tests\TestCase;

/**
 * BE-R23-09 (ai-vitalfy/action-plans/backend/R23.md): POST
 * /documents/generate de ponta a ponta (rota real, autenticação real, o
 * mesmo endpoint que o front chama) com FEATURE_CLINICAL_FACTS_EXTRACTION
 * ligada — com a flag de BE-R23-06 (asyncTranscriptPipeline) também ligada,
 * já que é o pipeline assíncrono quem chama a etapa de fatos.
 */
class DocumentGenerateWithFactsTest extends TestCase
{
    use RefreshDatabase;
    use InteractsWithAsyncPipelineFlag;
    use InteractsWithClinicalFactsExtractionFlag;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setAsyncTranscriptPipeline(true);
        $this->setClinicalFactsExtraction(true);
    }

    protected function tearDown(): void
    {
        $this->restoreAsyncTranscriptPipeline();
        $this->restoreClinicalFactsExtraction();

        parent::tearDown();
    }

    private function createTranscriptFor(User $owner): Transcript
    {
        $type = TranscriptType::create(['type' => 'Consulta']);

        return Transcript::create([
            'user_id' => $owner->id,
            'transcript_type_id' => $type->id,
            'patient' => 'Paciente Teste',
            'conversation' => [
                ['speaker' => 1, 'text' => 'Paciente relata dor no peito há três dias.'],
            ],
            'status' => TranscriptStatusEnum::Completed,
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
            ],
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

    public function test_flag_ligada_produz_documento_e_clinical_facts(): void
    {
        Queue::fake([ProcessGenerateInsightsAI::class]);
        Event::fake([TranscriptCreated::class]);

        $owner = User::factory()->create();
        $transcript = $this->createTranscriptFor($owner);
        $template = $this->createTemplate();

        $this->mock(ClinicalFactsExtractor::class, function ($mock) use ($template) {
            $mock->shouldReceive('extract')->once()->andReturn([
                'schema_version' => 'clinical-facts/1',
                'template_id' => $template->id,
                'title' => ['text' => 'Dor torácica', 'source_key' => 'queixa_principal'],
                'sections' => [[
                    'key' => 'queixa_principal',
                    'items' => [[
                        'text' => 'Paciente refere dor torácica há três dias.',
                        'status' => 'relatado',
                        'speaker' => 1,
                        'evidence' => 'Paciente relata dor no peito há três dias',
                    ]],
                ]],
            ]);
        });
        $this->partialMock(DocumentService::class, fn ($mock) => $mock->shouldNotReceive('generateLlmDocument'));

        Sanctum::actingAs($owner);
        $response = $this->postJson('/api/documents/generate', $this->payload($transcript, $template));

        $response->assertStatus(202);

        $document = Document::where('transcript_id', $transcript->id)->first();
        $this->assertNotNull($document);
        $this->assertNull($document->facts_fallback_at);
        $this->assertStringContainsString('Paciente refere dor torácica há três dias.', $document->result);

        $this->assertNotNull($transcript->fresh()->clinical_facts);
    }

    public function test_extrator_falhando_produz_documento_via_fallback_com_facts_fallback_at(): void
    {
        Queue::fake([ProcessGenerateInsightsAI::class]);
        Event::fake([TranscriptCreated::class]);

        $owner = User::factory()->create();
        $transcript = $this->createTranscriptFor($owner);
        $template = $this->createTemplate();

        $this->mock(ClinicalFactsExtractor::class, function ($mock) {
            $mock->shouldReceive('extract')->once()->andThrow(new ClinicalFactsExtractionException('Groq indisponível'));
        });
        $this->mock(DocumentService::class, function ($mock) {
            $mock->shouldReceive('generateLlmDocument')->once()->andReturn('<p>documento via fallback</p>');
        });

        Sanctum::actingAs($owner);
        $response = $this->postJson('/api/documents/generate', $this->payload($transcript, $template));

        $response->assertStatus(202);

        $document = Document::where('transcript_id', $transcript->id)->first();
        $this->assertNotNull($document);
        $this->assertSame('<p>documento via fallback</p>', $document->result);
        $this->assertNotNull($document->facts_fallback_at);
        $this->assertNull($transcript->fresh()->clinical_facts);
    }
}
