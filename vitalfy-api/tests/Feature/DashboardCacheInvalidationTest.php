<?php

namespace Tests\Feature;

use App\Models\Transcript;
use App\Models\TranscriptType;
use App\Models\User;
use App\Services\DashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * BE-R4-02 (ai-vitalfy/action-plans/backend/R4.md): DashboardService::clear()
 * apagava chaves de cache que `charts()` nunca gravou (período embutido na
 * chave, quando `currentWeekTranscripts()`/`countWeekTranscriptByType()`
 * gravam sem período) — o cache de "transcrições por tipo na semana" nunca
 * era invalidado ao criar/apagar uma transcrição, ficando até 10 minutos
 * (TTL de `countWeekTranscriptByType`) desatualizado.
 */
class DashboardCacheInvalidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_criar_transcricao_invalida_o_cache_de_transcricoes_por_tipo_na_semana(): void
    {
        $user = User::factory()->create();
        $type = TranscriptType::firstOrCreate(['type' => 'Consulta']);

        $this->actingAs($user);

        $before = app(DashboardService::class)->charts();
        $countBefore = collect($before['transcriptsByType'])->firstWhere('id', $type->id)['transcripts_count'];

        Transcript::create([
            'user_id' => $user->id,
            'transcript_type_id' => $type->id,
            'patient' => 'Paciente Teste',
            'conversation' => ['speaker' => 'médico', 'text' => 'teste'],
        ]);

        $after = app(DashboardService::class)->charts();
        $countAfter = collect($after['transcriptsByType'])->firstWhere('id', $type->id)['transcripts_count'];

        $this->assertSame(
            $countBefore + 1,
            $countAfter,
            'o cache de transcrições por tipo deveria refletir a transcrição recém-criada, não o valor cacheado antigo'
        );
    }

    public function test_apagar_transcricao_invalida_o_cache_de_transcricoes_por_tipo_na_semana(): void
    {
        $user = User::factory()->create();
        $type = TranscriptType::firstOrCreate(['type' => 'Consulta']);

        $transcript = Transcript::create([
            'user_id' => $user->id,
            'transcript_type_id' => $type->id,
            'patient' => 'Paciente Teste',
            'conversation' => ['speaker' => 'médico', 'text' => 'teste'],
        ]);

        $this->actingAs($user);

        $before = app(DashboardService::class)->charts();
        $countBefore = collect($before['transcriptsByType'])->firstWhere('id', $type->id)['transcripts_count'];

        $transcript->delete();

        $after = app(DashboardService::class)->charts();
        $countAfter = collect($after['transcriptsByType'])->firstWhere('id', $type->id)['transcripts_count'];

        $this->assertSame(
            $countBefore - 1,
            $countAfter,
            'o cache de transcrições por tipo deveria refletir a exclusão, não o valor cacheado antigo'
        );
    }
}
