<?php

namespace App\Providers;

use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\SecurityScheme;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

/**
 * Provider da aplicação: configurações executadas na inicialização.
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Documenta na especificação OpenAPI que a API usa token Bearer.
        Scramble::configure()->withDocumentTransformers(function (OpenApi $openApi) {
            $openApi->secure(SecurityScheme::http('bearer'));
        });

        // Limite de tentativas de login e cadastro (middleware `throttle:auth`).
        // A chave combina e-mail e IP: protege cada conta contra força bruta sem
        // bloquear todos os usuários atrás do mesmo IP (escritório, NAT, Docker).
        RateLimiter::for('auth', function (Request $request) {
            $key = Str::lower($request->string('email')->toString()).'|'.$request->ip();

            return Limit::perMinute((int) config('auth.throttle_per_minute'))->by($key);
        });
    }
}
