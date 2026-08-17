<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Sem isso, $request->ip() retorna o IP do hop anterior na rede Docker
        // (api-nginx), não o IP real do cliente encaminhado pela Cloudflare via
        // X-Forwarded-For — o que torna todo throttle por IP (auth, api,
        // transcripts, stream) efetivamente global/inútil (ver BE-R2-01).
        // '*' é seguro aqui: a API só é alcançável pelos proxies internos do
        // próprio compose, nunca diretamente pela internet.
        $middleware->trustProxies(
            at: '*',
            headers: Request::HEADER_X_FORWARDED_FOR
                | Request::HEADER_X_FORWARDED_HOST
                | Request::HEADER_X_FORWARDED_PORT
                | Request::HEADER_X_FORWARDED_PROTO,
        );

        // Injeta o token HttpOnly como Bearer header antes do Sanctum processar
        $middleware->prependToGroup('api', \App\Http\Middleware\TokenFromCookie::class);

        $middleware->alias([
            'check.subscription'      => \App\Http\Middleware\CheckSubscription::class,
            'free.transcript.limit'   => \App\Http\Middleware\CheckTranscriptLimit::class,
            'no.concurrent.transcript' => \App\Http\Middleware\PreventConcurrentTranscription::class,
            // R5 (ai-vitalfy/action-plans/backend/R5.md): substitui o
            // EnsureEmailIsVerified nativo do Laravel por um que devolve
            // `email_verification_required` no corpo — o 409 nativo seria
            // indistinguível dos outros 409 que o app já usa (documento
            // duplicado, transcrição concorrente).
            'verified'                => \App\Http\Middleware\EnsureEmailIsVerified::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
