<?php

namespace Tests\Feature;

use App\Models\Transcript;
use App\Models\TranscriptType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TranscriptOwnershipTest extends TestCase
{
    use RefreshDatabase;

    private const INEXISTENTE = '00000000-0000-0000-0000-000000000000';

    private function createTranscriptFor(User $owner): Transcript
    {
        $type = TranscriptType::firstOrCreate(['type' => 'Consulta']);

        return Transcript::create([
            'user_id' => $owner->id,
            'transcript_type_id' => $type->id,
            'patient' => 'Paciente Teste',
            'conversation' => ['speaker' => 'médico', 'text' => 'teste'],
        ]);
    }

    public function test_show_responde_403_para_nao_dono_e_404_para_inexistente(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $transcript = $this->createTranscriptFor($owner);

        Sanctum::actingAs($other);
        $this->getJson("/api/transcripts/{$transcript->id}")->assertStatus(403);

        Sanctum::actingAs($other);
        $this->getJson('/api/transcripts/' . self::INEXISTENTE)->assertStatus(404);
    }

    public function test_update_responde_403_para_nao_dono_e_404_para_inexistente(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $transcript = $this->createTranscriptFor($owner);

        Sanctum::actingAs($other);
        $this->putJson("/api/transcripts/{$transcript->id}", ['patient' => 'Outro nome'])
            ->assertStatus(403);

        Sanctum::actingAs($other);
        $this->putJson('/api/transcripts/' . self::INEXISTENTE, ['patient' => 'Outro nome'])
            ->assertStatus(404);

        $this->assertSame('Paciente Teste', $transcript->fresh()->patient);
    }

    public function test_delete_responde_403_para_nao_dono_e_404_para_inexistente(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $transcript = $this->createTranscriptFor($owner);

        Sanctum::actingAs($other);
        $this->deleteJson("/api/transcripts/{$transcript->id}")->assertStatus(403);

        Sanctum::actingAs($other);
        $this->deleteJson('/api/transcripts/' . self::INEXISTENTE)->assertStatus(404);

        $this->assertNull(Transcript::withTrashed()->find($transcript->id)->deleted_at);
    }

    public function test_get_conversations_responde_403_para_nao_dono_e_404_para_inexistente(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $transcript = $this->createTranscriptFor($owner);

        Sanctum::actingAs($other);
        $this->getJson("/api/transcripts/{$transcript->id}/conversations")->assertStatus(403);

        Sanctum::actingAs($other);
        $this->getJson('/api/transcripts/' . self::INEXISTENTE . '/conversations')->assertStatus(404);
    }

    public function test_dono_acessa_normalmente_os_quatro_endpoints(): void
    {
        $owner = User::factory()->create();
        $transcript = $this->createTranscriptFor($owner);

        Sanctum::actingAs($owner);
        $this->getJson("/api/transcripts/{$transcript->id}")->assertStatus(200);

        Sanctum::actingAs($owner);
        $this->getJson("/api/transcripts/{$transcript->id}/conversations")->assertStatus(200);

        Sanctum::actingAs($owner);
        $this->putJson("/api/transcripts/{$transcript->id}", ['patient' => 'Nome Atualizado'])
            ->assertStatus(200);
        $this->assertSame('Nome Atualizado', $transcript->fresh()->patient);

        Sanctum::actingAs($owner);
        $this->deleteJson("/api/transcripts/{$transcript->id}")->assertStatus(200);
    }
}
