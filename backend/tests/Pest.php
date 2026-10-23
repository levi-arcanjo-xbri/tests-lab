<?php

/**
 * Configuração global do Pest.
 *
 * - Testes de Feature sobem a aplicação e falam com o PostgreSQL de teste
 *   real (banco `estudo_test`, ver phpunit.xml). RefreshDatabase roda as
 *   migrations uma vez e envolve cada teste numa transação desfeita no fim,
 *   então um teste não enxerga os dados do outro.
 * - Testes de Unit também estendem o TestCase do Laravel (para ter helpers
 *   como config() e now()), mas não tocam no banco.
 */

use App\Domain\Auth\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

pest()->extend(TestCase::class)
    ->in('Unit');

/**
 * Cria um usuário e autentica as próximas requisições do teste como ele.
 * Sanctum::actingAs simula o token Bearer sem precisar criar um de verdade.
 */
function actingAsUser(?User $user = null): User
{
    $user ??= User::factory()->create();
    Sanctum::actingAs($user);

    return $user;
}
