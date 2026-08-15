<?php

namespace Tests\Feature;

use App\Mail\ResetPasswordMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

/**
 * BE-R4-03 (ai-vitalfy/action-plans/backend/R4.md): fluxo de reset de senha
 * sem nenhuma cobertura até esta tarefa.
 */
class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(ThrottleRequests::class);
    }

    public function test_solicitar_reset_para_email_existente_envia_o_email(): void
    {
        Mail::fake();
        $user = User::factory()->create(['email' => 'existe@example.com']);

        $response = $this->postJson('/api/forgot-password', ['email' => 'existe@example.com']);

        $response->assertStatus(200);
        Mail::assertSent(ResetPasswordMail::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });
    }

    public function test_solicitar_reset_para_email_inexistente_responde_422_sem_enviar_email(): void
    {
        Mail::fake();

        $response = $this->postJson('/api/forgot-password', ['email' => 'nao-existe@example.com']);

        $response->assertStatus(422);
        Mail::assertNothingSent();
    }

    public function test_reset_com_token_invalido_responde_422(): void
    {
        $user = User::factory()->create(['email' => 'reset@example.com']);

        $response = $this->postJson('/api/reset-password', [
            'token' => 'token-invalido',
            'email' => $user->email,
            'password' => 'senha-nova-123',
            'password_confirmation' => 'senha-nova-123',
        ]);

        $response->assertStatus(422);
        $response->assertJson(['message' => 'Token inválido ou expirado.']);
    }

    public function test_reset_com_token_valido_redefine_a_senha_e_revoga_tokens_existentes(): void
    {
        $user = User::factory()->create([
            'email' => 'reset-ok@example.com',
            'password' => Hash::make('senha-original'),
        ]);
        $user->createToken('auth_token');
        $this->assertSame(1, $user->tokens()->count());

        $token = Password::broker()->createToken($user);

        $response = $this->postJson('/api/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'senha-nova-123',
            'password_confirmation' => 'senha-nova-123',
        ]);

        $response->assertStatus(200);
        $this->assertTrue(Hash::check('senha-nova-123', $user->fresh()->password));
        $this->assertSame(0, $user->fresh()->tokens()->count());
    }
}
