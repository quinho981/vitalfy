<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * R5 (ai-vitalfy/action-plans/backend/R5.md, BE-R5-04): a migration de
 * grandfather roda sozinha, no schema setup, contra um banco de teste vazio
 * (0 usuários) — não exercita a lógica de verdade. Este teste chama a
 * migration diretamente contra linhas inseridas manualmente, simulando
 * contas reais que existiam antes do corte.
 */
class GrandfatherVerifiedUsersMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_conta_nao_verificada_criada_antes_do_corte_recebe_created_at_como_data_de_verificacao(): void
    {
        $createdAt = now()->subDays(10);

        $jaVerificado = User::factory()->create();
        $verifiedAtOriginal = $jaVerificado->email_verified_at;

        $naoVerificado = User::factory()->unverified()->create();
        $naoVerificado->forceFill(['created_at' => $createdAt])->save();
        $this->assertNull($naoVerificado->fresh()->email_verified_at);

        $migration = require database_path('migrations/2026_08_17_164250_grandfather_existing_unverified_users_at_r5_cutover.php');
        $migration->up();

        $this->assertEquals($createdAt->timestamp, $naoVerificado->fresh()->email_verified_at->timestamp);
        $this->assertEquals(
            $verifiedAtOriginal->timestamp,
            $jaVerificado->fresh()->email_verified_at->timestamp,
            'a migration não deve tocar quem já estava verificado'
        );
    }
}
