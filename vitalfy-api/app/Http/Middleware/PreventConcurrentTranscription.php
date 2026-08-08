<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * BE-R1-04 (ai-vitalfy/action-plans/backend/R1.md): throttle:transcripts
 * limita taxa por janela, não concorrência simultânea — é a concorrência que
 * esgota o pool php-fpm compartilhado (login, dashboard etc. morrem junto).
 * Lock por usuário, não global: não impede usuários diferentes de processar
 * ao mesmo tempo, só impede o mesmo usuário de empilhar processamentos.
 */
class PreventConcurrentTranscription
{
    /**
     * TTL do lock: rede de segurança para o caso de o `finally` não rodar
     * (worker morto, OOM). Levemente acima do teto de 180s da cadeia de
     * nginx (BE-R1-01) — nunca deveria ser atingido em operação normal.
     */
    private const LOCK_SECONDS = 200;

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $lock = Cache::lock("transcript-processing:{$user->id}", self::LOCK_SECONDS);

        if (! $lock->get()) {
            return response()->json([
                'success' => false,
                'message' => 'Você já tem um processamento em andamento. Aguarde a conclusão antes de enviar outro áudio.',
            ], 409);
        }

        try {
            return $next($request);
        } finally {
            $lock->release();
        }
    }
}
