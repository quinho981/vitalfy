<?php

namespace Tests\Feature;

use App\Enums\TranscriptStatusEnum;
use App\Models\Transcript;
use App\Models\TranscriptType;
use App\Models\User;
use App\Services\TranscriptService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Critério de conclusão da decisão de cota registrada em BE-R1-06
 * (ai-vitalfy/action-plans/shared/R1.md#sh-r1-01): a cota mensal é debitada
 * na conclusão (status completed), não no envio — processamento em curso ou
 * que falhou não consome a cota do usuário.
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
}
