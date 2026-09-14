<?php

namespace Tests\Feature;

use App\Exceptions\InvalidMedicalAnalysisException;
use App\Jobs\ProcessGenerateInsightsAI;
use App\Models\Document;
use App\Models\DocumentTemplate;
use App\Models\DocumentTemplateCategory;
use App\Enums\TranscriptStatusEnum;
use App\Models\Transcript;
use App\Models\TranscriptType;
use App\Models\User;
use App\Services\DocumentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * A extração factual e os insights do mesmo documento disputam a mesma
 * janela de 60s do TPM do Groq (ai-vitalfy/CAPACITY.md), e caem com
 * segundos de diferença. Com `tries = 1` — o default do supervisor em
 * config/horizon.php — o primeiro 429 apagava os insights daquele documento
 * para sempre.
 *
 * O que estes testes travam é a distinção entre as duas classes de falha:
 * rate limit e indisponibilidade merecem retentativa; contrato violado pelo
 * modelo, não — retentar gasta o mesmo token para receber o mesmo payload
 * inválido.
 */
class InsightsRetryPolicyTest extends TestCase
{
    use RefreshDatabase;

    private function documento(): Document
    {
        $user = User::factory()->create();
        $category = DocumentTemplateCategory::create(['name' => 'Categoria Teste']);
        $template = DocumentTemplate::create([
            'name' => 'Modelo Teste',
            'category_id' => $category->id,
            'content' => '<h2>{titulo}</h2>{context}',
        ]);
        $transcript = Transcript::create([
            'user_id' => $user->id,
            'transcript_type_id' => TranscriptType::create(['type' => 'Consulta'])->id,
            'patient' => 'Paciente Teste',
            'conversation' => [['speaker' => 1, 'text' => 'Paciente relata dor.']],
            'status' => TranscriptStatusEnum::Completed,
        ]);

        return $transcript->document()->create([
            'document_template_id' => $template->id,
            'patient' => 'Paciente Teste',
            'result' => '<p>documento</p>',
        ]);
    }

    public function test_job_tenta_de_novo_com_intervalo_que_cobre_a_janela_do_tpm(): void
    {
        $job = new ProcessGenerateInsightsAI('id-qualquer', []);

        $this->assertSame(3, $job->tries, 'um 429 não pode mais apagar os insights de vez');

        $backoff = $job->backoff();

        $this->assertCount(2, $backoff);
        $this->assertGreaterThan(
            60,
            array_sum($backoff),
            'a soma dos intervalos precisa ultrapassar a janela de 60s do TPM'
        );
    }

    /**
     * Se o modelo devolver um payload fora do contrato, as três tentativas
     * produziriam três payloads igualmente inválidos, gastando token à toa.
     * O job precisa falhar na primeira.
     */
    public function test_contrato_violado_falha_na_hora_sem_consumir_as_retentativas(): void
    {
        $document = $this->documento();

        $this->partialMock(DocumentService::class, function ($mock) {
            $mock->shouldReceive('generateInsightsAI')
                ->once()
                ->andThrow(new InvalidMedicalAnalysisException('case_severity fora do enum esperado'));
        });

        $job = new ProcessGenerateInsightsAI($document->id, [['text' => 'paciente: dor.']]);
        $job->handle(app(DocumentService::class));

        $this->assertNull(
            $document->fresh()->ai_insights,
            'nenhum insight deveria ter sido persistido'
        );
    }
}
