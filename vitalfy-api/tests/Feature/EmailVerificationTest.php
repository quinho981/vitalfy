<?php

namespace Tests\Feature;

use App\Mail\WelcomeVerificationMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * BE-R4-03 (ai-vitalfy/action-plans/backend/R4.md): verificação de e-mail
 * (link assinado) e reenvio sem nenhuma cobertura até esta tarefa.
 */
class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    private function verificationUrl(User $user): string
    {
        return URL::temporarySignedRoute(
            'verification.verify',
            now()->addHour(),
            ['id' => $user->getKey(), 'hash' => sha1($user->email)]
        );
    }

    public function test_hash_invalido_redireciona_para_status_invalido(): void
    {
        $user = User::factory()->unverified()->create();
        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addHour(),
            ['id' => $user->getKey(), 'hash' => sha1('email-errado@example.com')]
        );

        $response = $this->get($url);

        $response->assertRedirect(config('app.frontend_url') . '/auth/email-verified?status=invalid');
        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_usuario_inexistente_redireciona_para_status_invalido(): void
    {
        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addHour(),
            ['id' => '00000000-0000-0000-0000-000000000000', 'hash' => sha1('qualquer@example.com')]
        );

        $response = $this->get($url);

        $response->assertRedirect(config('app.frontend_url') . '/auth/email-verified?status=invalid');
    }

    public function test_usuario_ja_verificado_redireciona_para_status_already_verified(): void
    {
        $user = User::factory()->create();

        $response = $this->get($this->verificationUrl($user));

        $response->assertRedirect(config('app.frontend_url') . '/auth/email-verified?status=already_verified');
    }

    public function test_hash_valido_marca_email_como_verificado_e_redireciona_para_status_success(): void
    {
        $user = User::factory()->unverified()->create();

        $response = $this->get($this->verificationUrl($user));

        $response->assertRedirect(config('app.frontend_url') . '/auth/email-verified?status=success');
        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_reenvio_para_usuario_ja_verificado_responde_422(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);
        $response = $this->postJson('/api/email/resend-verification');

        $response->assertStatus(422);
    }

    public function test_reenvio_para_usuario_nao_verificado_envia_novo_email(): void
    {
        Mail::fake();
        $user = User::factory()->unverified()->create();

        Sanctum::actingAs($user);
        $response = $this->postJson('/api/email/resend-verification');

        $response->assertStatus(200);
        // sendEmailVerificationNotification() usa Mail::queue(), não send() —
        // MailFake trata os dois separadamente.
        Mail::assertQueued(WelcomeVerificationMail::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });
    }
}
