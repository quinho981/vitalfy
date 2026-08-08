<?php

namespace Tests\Feature;

use App\Enums\TranscriptStatusEnum;
use App\Models\Transcript;
use App\Models\TranscriptType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Critério de conclusão de BE-R1-07 (ai-vitalfy/action-plans/backend/R1.md):
 * status consultável (processando, concluído, falhou) e 404 para não-dono,
 * no mesmo padrão de DocumentInsightsTest para o R2.
 */
class TranscriptStatusTest extends TestCase
{
    use RefreshDatabase;

    private function createTranscriptFor(User $owner, TranscriptStatusEnum $status, ?string $failureReason = null): Transcript
    {
        $type = TranscriptType::create(['type' => 'Consulta']);

        return Transcript::create([
            'user_id' => $owner->id,
            'transcript_type_id' => $type->id,
            'patient' => 'Paciente Teste',
            'conversation' => $status === TranscriptStatusEnum::Pending ? null : ['speaker' => 'médico', 'text' => 'teste'],
            'status' => $status,
            'failure_reason' => $failureReason,
        ]);
    }

    public function test_sem_cookie_responde_401(): void
    {
        $owner = User::factory()->create();
        $transcript = $this->createTranscriptFor($owner, TranscriptStatusEnum::Pending);

        $response = $this->getJson("/api/transcripts/{$transcript->id}/status");

        $response->assertStatus(401);
    }

    public function test_nao_dono_responde_404(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $transcript = $this->createTranscriptFor($owner, TranscriptStatusEnum::Pending);

        Sanctum::actingAs($other);

        $response = $this->getJson("/api/transcripts/{$transcript->id}/status");

        $response->assertStatus(404);
    }

    public function test_inexistente_responde_404(): void
    {
        $owner = User::factory()->create();
        Sanctum::actingAs($owner);

        $response = $this->getJson('/api/transcripts/00000000-0000-0000-0000-000000000000/status');

        $response->assertStatus(404);
    }

    public function test_dono_ve_processando(): void
    {
        $owner = User::factory()->create();
        $transcript = $this->createTranscriptFor($owner, TranscriptStatusEnum::Transcribing);

        Sanctum::actingAs($owner);

        $response = $this->getJson("/api/transcripts/{$transcript->id}/status");

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'transcribing',
            'transcript_id' => null,
            'failure_reason' => null,
        ]);
    }

    public function test_dono_ve_concluido_com_transcript_id(): void
    {
        $owner = User::factory()->create();
        $transcript = $this->createTranscriptFor($owner, TranscriptStatusEnum::Completed);

        Sanctum::actingAs($owner);

        $response = $this->getJson("/api/transcripts/{$transcript->id}/status");

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'completed',
            'transcript_id' => $transcript->id,
        ]);
    }

    public function test_dono_ve_falha_definitiva_como_nao_recuperavel(): void
    {
        $owner = User::factory()->create();
        $transcript = $this->createTranscriptFor(
            $owner,
            TranscriptStatusEnum::Failed,
            'O áudio excede o limite máximo de 30 minutos. Duração atual: 2000 segundos.'
        );

        Sanctum::actingAs($owner);

        $response = $this->getJson("/api/transcripts/{$transcript->id}/status");

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'failed',
            'recoverable' => false,
        ]);
    }

    public function test_dono_ve_falha_de_api_externa_como_recuperavel(): void
    {
        $owner = User::factory()->create();
        $transcript = $this->createTranscriptFor(
            $owner,
            TranscriptStatusEnum::Failed,
            'Não foi possível concluir o processamento. Tente enviar o áudio novamente.'
        );

        Sanctum::actingAs($owner);

        $response = $this->getJson("/api/transcripts/{$transcript->id}/status");

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'failed',
            'recoverable' => true,
        ]);
    }
}
