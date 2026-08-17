<?php

namespace Tests\Feature;

use App\Jobs\SendOnboardingDayOneEmail;
use App\Jobs\SendProBenefitsReminderEmail;
use App\Jobs\SendTranscriptMonthlyReminderEmail;
use App\Mail\WelcomeVerificationMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Tests\TestCase;

/**
 * R5 (ai-vitalfy/action-plans/backend/R5.md, BE-R5-02): o Google já verifica
 * a posse do e-mail antes de devolver o callback — pedir para o usuário
 * verificar de novo é atrito redundante, e sem esta correção o corte de
 * BE-R5-04 criaria uma barreira nova para todo cadastro social.
 */
class SocialAuthControllerTest extends TestCase
{
    use RefreshDatabase;

    private function fakeGoogleUser(string $id, string $email, string $name = 'Fulano da Silva'): SocialiteUser
    {
        return (new SocialiteUser())->map([
            'id' => $id,
            'name' => $name,
            'email' => $email,
            'avatar' => 'https://example.com/avatar.png',
        ]);
    }

    public function test_conta_nova_via_google_nasce_verificada_e_nao_recebe_email_de_verificacao(): void
    {
        Mail::fake();
        Bus::fake([SendOnboardingDayOneEmail::class, SendProBenefitsReminderEmail::class, SendTranscriptMonthlyReminderEmail::class]);

        Socialite::fake('google', $this->fakeGoogleUser('google-123', 'novo-google@example.com'));

        $response = $this->get('/api/auth/google/callback');

        $response->assertRedirect();
        $this->assertStringContainsString('social_auth=success', $response->headers->get('Location'));

        $user = User::where('email', 'novo-google@example.com')->first();
        $this->assertNotNull($user);
        $this->assertNotNull($user->email_verified_at);
        $this->assertTrue($user->hasVerifiedEmail());

        Mail::assertNotQueued(WelcomeVerificationMail::class);
        Mail::assertNotSent(WelcomeVerificationMail::class);

        // A sequência de onboarding continua valendo — só o e-mail de
        // verificação (redundante para quem já veio verificado) é suprimido.
        Bus::assertDispatched(SendOnboardingDayOneEmail::class);
        Bus::assertDispatched(SendProBenefitsReminderEmail::class);
        Bus::assertDispatched(SendTranscriptMonthlyReminderEmail::class);
    }

    public function test_conta_email_senha_nao_verificada_e_verificada_ao_logar_com_google_do_mesmo_email(): void
    {
        $user = User::factory()->unverified()->create(['email' => 'ja-cadastrado@example.com']);
        $this->assertNull($user->email_verified_at);

        Socialite::fake('google', $this->fakeGoogleUser('google-456', 'ja-cadastrado@example.com'));

        $response = $this->get('/api/auth/google/callback');

        $response->assertRedirect();

        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $this->assertSame('google-456', $user->fresh()->google_id);
    }

    public function test_conta_ja_verificada_permanece_verificada_ao_logar_com_google(): void
    {
        $user = User::factory()->create(['email' => 'verificado@example.com']);
        $verifiedAt = $user->email_verified_at;

        Socialite::fake('google', $this->fakeGoogleUser('google-789', 'verificado@example.com'));

        $this->get('/api/auth/google/callback');

        $this->assertEquals($verifiedAt->timestamp, $user->fresh()->email_verified_at->timestamp);
    }

    public function test_cadastro_por_email_senha_continua_nao_verificado_e_recebe_email_de_verificacao(): void
    {
        Mail::fake();

        $response = $this->postJson('/api/register', [
            'name' => 'Paciente Teste',
            'email' => 'email-senha@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201);

        $user = User::where('email', 'email-senha@example.com')->first();
        $this->assertNull($user->email_verified_at);

        Mail::assertQueued(WelcomeVerificationMail::class, fn ($mail) => $mail->hasTo($user->email));
    }
}
