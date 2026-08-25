<?php

namespace Tests\Feature;

use App\Enums\TranscriptStatusEnum;
use App\Models\Transcript;
use App\Models\TranscriptType;
use App\Models\User;
use App\Services\TranscriptService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Critério de conclusão da decisão de cota registrada em BE-R1-06
 * (ai-vitalfy/action-plans/shared/R1.md#sh-r1-01): a cota mensal é debitada
 * na conclusão (status completed), não no envio — processamento em curso ou
 * que falhou não consome a cota do usuário. Apagar uma transcrição completed
 * não devolve a cota (R11, ai-vitalfy/risks.md) — o trabalho já foi pago.
 */
class TranscriptQuotaCompletedOnlyTest extends TestCase
{
    use RefreshDatabase;

    private function createTranscript(User $user, TranscriptStatusEnum $status): Transcript
    {
        $type = TranscriptType::firstOrCreate(['type' => 'Consulta']);

        return Transcript::create([
            'user_id' => $user->id,
            'transcript_type_id' => $type->id,
            'patient' => 'Paciente Teste',
            'conversation' => ['speaker' => 'médico', 'text' => 'teste'],
            'status' => $status,
        ]);
    }

    public function test_transcricoes_nao_concluidas_nao_consomem_cota(): void
    {
        $user = User::factory()->create();

        $this->createTranscript($user, TranscriptStatusEnum::Pending);
        $this->createTranscript($user, TranscriptStatusEnum::Transcribing);
        $this->createTranscript($user, TranscriptStatusEnum::Generating);
        $this->createTranscript($user, TranscriptStatusEnum::Failed);

        $remaining = app(TranscriptService::class)->getRemainingMonthlyTranscripts($user->id);

        $this->assertSame(10, $remaining, 'nenhum status não-terminal-de-sucesso deveria debitar a cota gratuita de 10/mês');
    }

    public function test_transcricoes_concluidas_consomem_cota(): void
    {
        $user = User::factory()->create();

        $this->createTranscript($user, TranscriptStatusEnum::Completed);
        $this->createTranscript($user, TranscriptStatusEnum::Completed);
        $this->createTranscript($user, TranscriptStatusEnum::Failed);

        $remaining = app(TranscriptService::class)->getRemainingMonthlyTranscripts($user->id);

        $this->assertSame(8, $remaining);
    }

    /**
     * Regressão de R11 (ai-vitalfy/risks.md): GET /user calculava `remaining`
     * com uma query própria, divergente de CheckTranscriptLimit e
     * getRemainingMonthlyTranscripts. Este teste reproduz o cenário que
     * expunha a divergência: uma transcrição apagada continua contando
     * contra a cota (não é "falhada", que de fato não conta).
     */
    public function test_remaining_exibido_em_get_user_segue_a_mesma_regra_do_limite(): void
    {
        $user = User::factory()->create();

        $this->createTranscript($user, TranscriptStatusEnum::Completed);
        $trashed = $this->createTranscript($user, TranscriptStatusEnum::Completed);
        $trashed->delete();
        $this->createTranscript($user, TranscriptStatusEnum::Failed);

        Sanctum::actingAs($user);
        $response = $this->getJson('/api/user');

        $response->assertOk();
        $this->assertSame(8, $response->json('remaining'));
    }

    /**
     * Cenário relatado pelo usuário ao revisar R11: apagar transcrições não
     * pode devolver cota — senão um usuário no limite apaga as 10 e ganha
     * mais 10 no mesmo mês, sem custo adicional real para ele mas com custo
     * real já pago (Deepgram/Groq) para o produto.
     */
    public function test_apagar_transcricoes_completed_nao_devolve_a_cota(): void
    {
        $user = User::factory()->create();

        $transcripts = collect(range(1, 10))
            ->map(fn () => $this->createTranscript($user, TranscriptStatusEnum::Completed));

        $remainingBeforeDelete = app(TranscriptService::class)->getRemainingMonthlyTranscripts($user->id);
        $this->assertSame(0, $remainingBeforeDelete);

        $transcripts->each->delete();

        $remainingAfterDelete = app(TranscriptService::class)->getRemainingMonthlyTranscripts($user->id);
        $this->assertSame(0, $remainingAfterDelete, 'apagar as 10 transcrições não pode devolver a cota mensal');

        Sanctum::actingAs($user);
        $response = $this->postJson('/api/transcripts', []);
        $response->assertStatus(429, 'o middleware precisa continuar bloqueando mesmo com as 10 transcrições apagadas');
    }
}
