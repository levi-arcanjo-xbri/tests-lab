<?php

/**
 * Configuração da idempotência (ver App\Infrastructure\Idempotency).
 *
 * Prazos vêm de configuração, não de valores fixos no código, para poderem
 * mudar por ambiente (.env) e serem controlados nos testes.
 */
return [
    // Por quantas horas uma Idempotency-Key é lembrada antes de ser apagada.
    'ttl_hours' => (int) env('IDEMPOTENCY_TTL_HOURS', 24),
];
