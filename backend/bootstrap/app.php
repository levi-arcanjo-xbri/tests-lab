<?php

/**
 * Ponto de partida da aplicação Laravel 12: rotas, middlewares e tratamento
 * de exceções são configurados aqui (substitui os antigos Kernel.php).
 */

use App\Infrastructure\Idempotency\EnsureIdempotency;
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
    ->withMiddleware(function (Middleware $middleware): void {
        // Apelido usado nas rotas: ->middleware('idempotent').
        $middleware->alias([
            'idempotent' => EnsureIdempotency::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Toda rota /api responde erros em JSON ({ "message": ..., "errors": ... }),
        // mesmo que o cliente não envie Accept: application/json.
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
