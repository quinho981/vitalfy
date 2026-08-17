<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * R5 (ai-vitalfy/action-plans/backend/R5.md, BE-R5-04): substitui o
 * `\Illuminate\Auth\Middleware\EnsureEmailIsVerified` nativo — que também
 * devolveria 409 para JSON — porque este mesmo status já é usado por outras
 * verificações no app (transcrição concorrente em
 * PreventConcurrentTranscription, documento duplicado em
 * DocumentController::generate). Sem um campo que distinga o motivo, o
 * front não teria como saber se o 409 pede verificação de e-mail ou é só
 * "já existe/já está em andamento" — mesmo padrão de `requires_pro` em
 * CheckSubscription.
 */
class EnsureEmailIsVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'E-mail não verificado. Confirme seu e-mail para continuar.',
                'email_verification_required' => true,
            ], 409);
        }

        return $next($request);
    }
}
