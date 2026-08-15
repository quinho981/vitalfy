<?php

namespace Tests\Feature;

use App\Enums\TranscriptStatusEnum;
use App\Models\Subscription;
use App\Models\Transcript;
use App\Models\TranscriptType;
use App\Models\User;
use App\Support\PlanLimits;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * BE-R4-05 (ai-vitalfy/action-plans/backend/R4.md): `CheckTranscriptLimit`
 * já tinha cobertura indireta do cálculo (`TranscriptQuotaCompletedOnlyTest`,
 * via `TranscriptService`) mas nenhuma do middleware aplicado à rota HTTP.
 *
 * Corpo da requisição vazio (`[]`) é proposital: sem áudio, a FormRequest
 * sempre rejeita com 422 — o mesmo truque de `TranscriptConcurrencyLockTest`.
 * Isso isola exatamente o que o middleware decide (barrar com 429 antes de
 * qualquer outra coisa, ou deixar passar) sem precisar simular Deepgram.
 */
class CheckTranscriptLimitTest extends TestCase
{
    use RefreshDatabase;

    private function createCompletedTranscript(User $user): Transcript
    {
        $type = TranscriptType::firstOrCreate(['type' => 'Consulta']);

        return Transcript::create([
            'user_id' => $user->id,
            'transcript_type_id' => $type->id,
            'patient' => 'Paciente Teste',
            'conversation' => ['speaker' => 'médico', 'text' => 'teste'],
            'status' => TranscriptStatusEnum::Completed,
        ]);
    }

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

    public function test_usuario_free_no_limite_mensal_recebe_429(): void
    {
        $user = User::factory()->create();
        for ($i = 0; $i < PlanLimits::FREE_MONTHLY_TRANSCRIPTS; $i++) {
            $this->createCompletedTranscript($user);
        }

        Sanctum::actingAs($user);
        $response = $this->postJson('/api/transcripts', []);

        $response->assertStatus(429);
    }

    public function test_usuario_free_abaixo_do_limite_atravessa_o_middleware(): void
    {
        $user = User::factory()->create();
        for ($i = 0; $i < PlanLimits::FREE_MONTHLY_TRANSCRIPTS - 1; $i++) {
            $this->createCompletedTranscript($user);
        }

        Sanctum::actingAs($user);
        $response = $this->postJson('/api/transcripts', []);

        // Não bloqueado pelo limite — falha adiante na FormRequest (422),
        // não no middleware (429).
        $response->assertStatus(422);
    }

    public function test_usuario_pro_nunca_e_bloqueado_mesmo_acima_do_limite_free(): void
    {
        $user = User::factory()->create();
        $this->giveActiveSubscription($user);

        for ($i = 0; $i < PlanLimits::FREE_MONTHLY_TRANSCRIPTS + 5; $i++) {
            $this->createCompletedTranscript($user);
        }

        Sanctum::actingAs($user);
        $response = $this->postJson('/api/transcripts', []);

        $response->assertStatus(422);
    }

    public function test_assinatura_com_ends_at_no_passado_nao_conta_como_pro(): void
    {
        $user = User::factory()->create();
        Subscription::create([
            'user_id' => $user->id,
            'type' => 'default',
            'stripe_id' => 'sub_' . uniqid(),
            'stripe_status' => 'active',
            'ends_at' => now()->subDay(),
        ]);

        for ($i = 0; $i < PlanLimits::FREE_MONTHLY_TRANSCRIPTS; $i++) {
            $this->createCompletedTranscript($user);
        }

        Sanctum::actingAs($user);
        $response = $this->postJson('/api/transcripts', []);

        // Assinatura expirada não é Pro — volta a valer o limite gratuito.
        $response->assertStatus(429);
    }
}
