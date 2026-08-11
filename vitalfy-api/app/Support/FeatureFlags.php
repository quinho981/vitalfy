<?php

namespace App\Support;

class FeatureFlags
{
    /**
     * SH-R1-02 (ai-vitalfy/action-plans/shared/R1.md): o rollback deste corte
     * precisa acontecer sem deploy. `app` e `horizon` compartilham imagem e o
     * front é build estático — reiniciar/rebuildar os três para reverter
     * levaria minutos. Por isso esta flag é lida via env() direto, não via
     * config('features...'): chamadas a env() fora de arquivos de config/
     * continuam lendo o ambiente real em runtime mesmo com `config:cache`
     * ativo (ao contrário de valores dentro de config/*.php, que ficam
     * congelados no cache — foi exatamente isso que quebrou APP_URL em R16).
     * Alterar a variável no .env do servidor e nada mais já muda o
     * comportamento na próxima requisição.
     *
     * Default TRUE (revisado em 10/08/2026): o produto ainda não tem
     * usuários em produção — a premissa original da flag (rollout
     * progressivo sobre tráfego real que precisa ser protegido) não se
     * aplica ainda. O assíncrono é o comportamento pretendido; a flag
     * continua existindo como kill-switch (`FEATURE_ASYNC_TRANSCRIPT_PIPELINE=false`
     * volta ao caminho síncrono sem deploy), não como opt-in.
     */
    public static function asyncTranscriptPipeline(): bool
    {
        return filter_var(env('FEATURE_ASYNC_TRANSCRIPT_PIPELINE', true), FILTER_VALIDATE_BOOLEAN);
    }
}
