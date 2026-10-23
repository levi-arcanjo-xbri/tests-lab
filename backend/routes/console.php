<?php

/**
 * Comandos de console e agendamentos (scheduler).
 *
 * Em produção, um cron chama `php artisan schedule:run` a cada minuto e o
 * Laravel decide o que rodar. Em desenvolvimento: `php artisan schedule:work`.
 */

use App\Infrastructure\Idempotency\IdempotencyKey;
use Illuminate\Support\Facades\Schedule;

// Apaga as chaves de idempotência vencidas (ver IdempotencyKey::prunable()).
Schedule::command('model:prune', ['--model' => [IdempotencyKey::class]])->daily();
