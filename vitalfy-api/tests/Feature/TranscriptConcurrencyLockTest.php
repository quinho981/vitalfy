<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Critério de conclusão de BE-R1-04 (ai-vitalfy/action-plans/backend/R1.md):
 * um usuário não consegue manter mais de um generate-document simultâneo, e
 * a resposta é específica (409), não genérica.
 *
 * Duas requisições HTTP concorrentes de verdade não são reproduzíveis num
 * processo PHPUnit único e síncrono — o teste simula a concorrência
 * adquirindo o lock manualmente antes da requisição, do mesmo jeito que uma
 * segunda requisição real encontraria o Cache::lock já tomado.
 */
class TranscriptConcurrencyLockTest extends TestCase
{
    use RefreshDatabase;

    public function test_segunda_requisicao_com_lock_ja_tomado_responde_409(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $lock = Cache::lock("transcript-processing:{$user->id}", 200);
        $this->assertTrue($lock->get(), 'pré-condição: o teste precisa conseguir tomar o lock primeiro');

        $response = $this->postJson('/api/transcripts/generate-document', []);

        $response->assertStatus(409);
        $response->assertJson(['success' => false]);
    }

    public function test_sem_lock_concorrente_a_requisicao_passa_do_middleware(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/transcripts/generate-document', []);

        // Sem lock concorrente, a requisição atravessa o middleware e falha
        // mais adiante por falta do campo `audio` (422 da FormRequest) — não
        // pelo 409 do lock. Prova que o lock é liberado corretamente.
        $response->assertStatus(422);
    }

    public function test_lock_e_por_usuario_nao_global(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        Cache::lock("transcript-processing:{$userA->id}", 200)->get();

        Sanctum::actingAs($userB);
        $response = $this->postJson('/api/transcripts/generate-document', []);

        // userB não é afetado pelo lock de userA.
        $response->assertStatus(422);
    }
}
