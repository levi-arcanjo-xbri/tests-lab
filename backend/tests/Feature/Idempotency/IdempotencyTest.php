<?php

/**
 * Idempotência: reenviar a mesma requisição (mesma Idempotency-Key) produz
 * um único efeito. Inclui os casos de falha parcial: erro não "queima" a
 * chave, chave em andamento, chave reaproveitada em outra requisição.
 */

use App\Domain\Auth\Models\User;
use App\Domain\Project\Models\Project;
use App\Domain\Task\Models\Task;
use App\Domain\Task\Models\TaskStatusHistory;
use App\Infrastructure\Idempotency\IdempotencyKey;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->user = actingAsUser();
});

it('cria um único projeto quando a requisição é reenviada', function () {
    $headers = ['Idempotency-Key' => 'chave-1'];

    $first = $this->postJson('/api/projects', ['name' => 'Único'], $headers)->assertCreated();
    $second = $this->postJson('/api/projects', ['name' => 'Único'], $headers)->assertCreated();

    $second->assertHeader('Idempotent-Replayed', 'true');
    expect($second->json())->toBe($first->json())
        ->and(Project::query()->count())->toBe(1);
});

it('cria uma única task, com um único histórico, no reenvio', function () {
    $project = Project::factory()->for($this->user, 'owner')->create();
    $url = "/api/projects/{$project->id}/tasks";
    $headers = ['Idempotency-Key' => 'task-1'];

    $this->postJson($url, ['title' => 'Uma vez'], $headers)->assertCreated();
    $this->postJson($url, ['title' => 'Uma vez'], $headers)->assertCreated();

    expect(Task::query()->count())->toBe(1)
        ->and(TaskStatusHistory::query()->count())->toBe(1);
});

it('repete a resposta da transição em vez de responder 409 no reenvio', function () {
    $project = Project::factory()->for($this->user, 'owner')->create();
    $task = Task::factory()->for($project)->create();
    $url = "/api/projects/{$project->id}/tasks/{$task->id}/status";
    $headers = ['Idempotency-Key' => 'status-1'];

    $this->patchJson($url, ['status' => 'done'], $headers)->assertOk();
    $this->patchJson($url, ['status' => 'done'], $headers)
        ->assertOk()
        ->assertHeader('Idempotent-Replayed', 'true');

    expect(TaskStatusHistory::query()->count())->toBe(1);
});

it('recusa a mesma chave usada com outro corpo', function () {
    $headers = ['Idempotency-Key' => 'chave-2'];

    $this->postJson('/api/projects', ['name' => 'A'], $headers)->assertCreated();
    $this->postJson('/api/projects', ['name' => 'B'], $headers)->assertUnprocessable();

    expect(Project::query()->pluck('name')->all())->toBe(['A']);
});

it('libera a chave quando a requisição falha, permitindo corrigir e reenviar', function () {
    $headers = ['Idempotency-Key' => 'chave-3'];

    $this->postJson('/api/projects', ['name' => ''], $headers)->assertUnprocessable();
    expect(IdempotencyKey::query()->count())->toBe(0);

    // O corpo mudou, mas como a tentativa anterior falhou, a chave está livre.
    $this->postJson('/api/projects', ['name' => 'Corrigido'], $headers)->assertCreated();
});

it('responde 409 enquanto a requisição original ainda está em andamento', function () {
    // Simula uma reserva sem resposta, como se outra requisição estivesse executando.
    IdempotencyKey::query()->create([
        'user_id' => $this->user->id,
        'key' => 'chave-4',
        'method' => 'POST',
        'path' => 'api/projects',
        'request_hash' => hash('sha256', 'POST|api/projects|'.json_encode(['name' => 'X'])),
    ]);

    $this->postJson('/api/projects', ['name' => 'X'], ['Idempotency-Key' => 'chave-4'])->assertConflict();

    expect(Project::query()->count())->toBe(0);
});

it('isola as chaves por usuário', function () {
    $headers = ['Idempotency-Key' => 'mesma-chave'];
    $this->postJson('/api/projects', ['name' => 'Do primeiro'], $headers)->assertCreated();

    Sanctum::actingAs(User::factory()->create());
    $this->postJson('/api/projects', ['name' => 'Do primeiro'], $headers)
        ->assertCreated()
        ->assertHeaderMissing('Idempotent-Replayed');

    expect(Project::query()->count())->toBe(2);
});

it('apaga as chaves vencidas com model:prune', function () {
    $this->travelTo(now()->subHours(25));
    $this->postJson('/api/projects', ['name' => 'Antigo'], ['Idempotency-Key' => 'velha']);
    $this->travelBack();
    $this->postJson('/api/projects', ['name' => 'Recente'], ['Idempotency-Key' => 'nova']);

    $this->artisan('model:prune', ['--model' => [IdempotencyKey::class]])->assertSuccessful();

    expect(IdempotencyKey::query()->pluck('key')->all())->toBe(['nova']);
});
