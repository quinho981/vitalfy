<?php

namespace Tests\Feature;

use App\Models\Subscription;
use App\Models\User;
use App\Services\DocumentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * BE-R4-05 (ai-vitalfy/action-plans/backend/R4.md): `CheckSubscription`
 * (usada por `POST /documents/refine`) sem nenhuma cobertura até esta tarefa.
 */
class CheckSubscriptionTest extends TestCase
{
    use RefreshDatabase;

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

    public function test_usuario_sem_assinatura_recebe_403_requires_pro(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);
        $response = $this->postJson('/api/documents/refine', ['content' => 'texto']);

        $response->assertStatus(403);
        $response->assertJson(['requires_pro' => true]);
    }

    public function test_usuario_com_assinatura_ativa_passa_pelo_middleware(): void
    {
        $user = User::factory()->create();
        $this->giveActiveSubscription($user);

        $this->mock(DocumentService::class, function ($mock) {
            $mock->shouldReceive('refineDocument')
                ->once()
                ->andReturn('texto refinado');
        });

        Sanctum::actingAs($user);
        $response = $this->postJson('/api/documents/refine', ['content' => 'texto']);

        $response->assertStatus(200);
        $response->assertJson(['content' => 'texto refinado']);
    }
}
