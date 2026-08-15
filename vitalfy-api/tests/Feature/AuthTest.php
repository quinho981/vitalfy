<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * BE-R4-03 (ai-vitalfy/action-plans/backend/R4.md): fluxo de autenticação por
 * cookie sem nenhuma cobertura até esta tarefa (ver "sem cobertura em" em
 * ai-vitalfy/risks.md#r4).
 */
class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // throttle:auth é 5/min por IP (AppServiceProvider::boot()) com cache
        // em memória compartilhado entre testes no mesmo processo — não é o
        // que esta tarefa testa, então fica desligado para não travar testes
        // que fazem mais de uma tentativa.
        $this->withoutMiddleware(ThrottleRequests::class);
    }

    public function test_registro_cria_usuario_nao_verificado(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Paciente Teste',
            'email' => 'novo@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201);

        $user = User::where('email', 'novo@example.com')->first();
        $this->assertNotNull($user);
        $this->assertNull($user->email_verified_at);
    }

    public function test_registro_com_email_duplicado_falha_validacao(): void
    {
        User::factory()->create(['email' => 'existente@example.com']);

        $response = $this->postJson('/api/register', [
            'name' => 'Outro',
            'email' => 'existente@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('email');
    }

    public function test_login_com_credenciais_validas_retorna_cookies(): void
    {
        User::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'user@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200);
        $response->assertCookie('api_token');
        $response->assertCookie('logged_in');
        $response->assertCookie('client_nonce');
    }

    public function test_login_com_credenciais_invalidas_responde_401_sem_cookies(): void
    {
        User::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'user@example.com',
            'password' => 'senha-errada',
        ]);

        $response->assertStatus(401);
        $response->assertCookieMissing('api_token');
    }

    public function test_remember_true_produz_cookie_com_validade_maior_que_remember_false(): void
    {
        User::factory()->create([
            'email' => 'remember@example.com',
            'password' => Hash::make('password123'),
        ]);

        $semRemember = $this->postJson('/api/login', [
            'email' => 'remember@example.com',
            'password' => 'password123',
            'remember' => false,
        ]);

        $comRemember = $this->postJson('/api/login', [
            'email' => 'remember@example.com',
            'password' => 'password123',
            'remember' => true,
        ]);

        // decrypt=false: rotas de API não passam pelo middleware
        // EncryptCookies (só o grupo 'web' o inclui por padrão no Laravel
        // 11) — o valor do cookie aqui é o token em texto puro, não algo
        // que app('encrypter') saiba decifrar.
        $minutosSemRemember = $semRemember->getCookie('api_token', false)->getExpiresTime();
        $minutosComRemember = $comRemember->getCookie('api_token', false)->getExpiresTime();

        $this->assertGreaterThan($minutosSemRemember, $minutosComRemember);
    }

    public function test_logout_revoga_token_atual_e_limpa_cookies(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token');

        $response = $this->withHeader('Authorization', 'Bearer ' . $token->plainTextToken)
            ->postJson('/api/logout');

        $response->assertStatus(200);
        $response->assertCookieExpired('api_token');
        $response->assertCookieExpired('logged_in');
        $response->assertCookieExpired('client_nonce');

        $this->assertSame(0, $user->tokens()->count());
    }

    public function test_troca_de_senha_com_senha_atual_errada_falha_sem_alterar_senha(): void
    {
        $user = User::factory()->create(['password' => Hash::make('senha-original')]);
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/change-password', [
                'current_password' => 'senha-errada',
                'new_password' => 'senha-nova-123',
                'new_password_confirmation' => 'senha-nova-123',
            ]);

        $response->assertStatus(422);
        $this->assertTrue(Hash::check('senha-original', $user->fresh()->password));
    }

    public function test_troca_de_senha_com_senha_atual_correta_atualiza_a_senha(): void
    {
        $user = User::factory()->create(['password' => Hash::make('senha-original')]);
        // Bearer token, não Sanctum::actingAs(): este teste faz login HTTP de
        // verdade depois, e actingAs() substitui o guard padrão para toda a
        // vida do teste, quebrando Auth::attempt() (RequestGuard não tem
        // esse método) na chamada real a /api/login mais abaixo.
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/change-password', [
                'current_password' => 'senha-original',
                'new_password' => 'senha-nova-123',
                'new_password_confirmation' => 'senha-nova-123',
            ]);

        $response->assertStatus(200);
        $this->assertTrue(Hash::check('senha-nova-123', $user->fresh()->password));

        // A chamada acima passou pelo middleware auth:sanctum, que chama
        // Auth::shouldUse('sanctum') ao autenticar — isso troca o guard
        // *padrão* da aplicação (o app é reaproveitado entre requests dentro
        // do mesmo teste). Sem isso, Auth::attempt() abaixo (usado por
        // AuthController::login) resolve para o guard sanctum (RequestGuard,
        // sem método attempt()) em vez do guard web/session esperado.
        \Illuminate\Support\Facades\Auth::shouldUse('web');

        $loginComSenhaAntiga = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'senha-original',
        ]);
        $loginComSenhaAntiga->assertStatus(401);

        $loginComSenhaNova = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'senha-nova-123',
        ]);
        $loginComSenhaNova->assertStatus(200);
    }
}
