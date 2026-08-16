<?php

namespace Tests\Feature;

use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SubscriptionControllerTest extends TestCase
{
    use RefreshDatabase;

    private function giveActiveSubscription(User $user): Subscription
    {
        return Subscription::create([
            'user_id' => $user->id,
            'type' => 'default',
            'stripe_id' => 'sub_' . uniqid(),
            'stripe_status' => 'active',
            'ends_at' => null,
        ]);
    }

    public function test_index_sem_assinatura_retorna_plano_free(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);
        $response = $this->getJson('/api/subscription');

        $response->assertStatus(200);
        $response->assertJson([
            'has_subscription' => false,
            'subscription' => null,
            'plan' => ['name' => 'Free'],
        ]);
    }

    public function test_index_com_assinatura_ativa_retorna_a_assinatura(): void
    {
        $user = User::factory()->create();
        $subscription = $this->giveActiveSubscription($user);

        Sanctum::actingAs($user);
        $response = $this->getJson('/api/subscription');

        $response->assertStatus(200);
        $response->assertJson([
            'has_subscription' => true,
            'subscription' => ['id' => $subscription->id],
        ]);
    }

    public function test_cancel_sem_assinatura_ativa_responde_404(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);
        $response = $this->postJson('/api/subscription/cancel');

        $response->assertStatus(404);
        $response->assertJson(['message' => 'No active subscription found']);
    }

    public function test_checkout_com_plano_invalido_nao_reconhecido_pelo_enum(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);
        
        $response = $this->postJson('/api/subscription/checkout', ['plan' => 'plano-que-nao-existe']);

        $response->assertStatus(500);
    }

    public function test_verify_checkout_sem_session_id_responde_400(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);
        $response = $this->getJson('/api/subscription/verify-checkout');

        $response->assertStatus(400);
        $response->assertJson(['error' => 'Session ID not provided']);
    }
}
