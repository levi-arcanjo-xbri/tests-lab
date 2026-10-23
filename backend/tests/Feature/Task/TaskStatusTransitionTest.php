<?php

/**
 * Máquina de estados de ponta a ponta: transições válidas, recusa das
 * inválidas e atomicidade (estado e histórico gravados juntos ou nenhum).
 */

use App\Domain\Project\Models\Project;
use App\Domain\Task\Models\Task;
use App\Domain\Task\Models\TaskStatusHistory;

beforeEach(function () {
    $this->user = actingAsUser();
    $this->project = Project::factory()->for($this->user, 'owner')->create();
});

function statusUrl(Task $task): string
{
    return "/api/projects/{$task->project_id}/tasks/{$task->id}/status";
}

it('conclui uma task: grava completed_at e o histórico', function () {
    $this->travelTo('2026-10-23 10:00:00');
    $task = Task::factory()->for($this->project)->create();

    $this->patchJson(statusUrl($task), ['status' => 'done'])
        ->assertOk()
        ->assertJsonPath('data.status', 'done')
        ->assertJsonPath('data.allowed_transitions', ['todo']);

    $task->refresh();
    expect($task->status->value)->toBe('done')
        ->and($task->completed_at->toDateTimeString())->toBe('2026-10-23 10:00:00');

    $history = TaskStatusHistory::query()->sole();
    expect($history->from_status->value)->toBe('todo')
        ->and($history->to_status->value)->toBe('done');
});

it('reabre uma task concluída e limpa completed_at', function () {
    $task = Task::factory()->for($this->project)->done()->create();

    $this->patchJson(statusUrl($task), ['status' => 'todo'])->assertOk();

    expect($task->refresh()->completed_at)->toBeNull()
        ->and($task->status->value)->toBe('todo');
});

it('recusa transição inválida com 409, sem mudar o estado nem gravar histórico', function () {
    $task = Task::factory()->for($this->project)->done()->create();
    $completedAt = $task->completed_at;

    $this->patchJson(statusUrl($task), ['status' => 'done'])
        ->assertConflict()
        ->assertJsonPath('from', 'done')
        ->assertJsonPath('to', 'done');

    expect($task->refresh()->status->value)->toBe('done')
        ->and($task->completed_at->equalTo($completedAt))->toBeTrue()
        ->and(TaskStatusHistory::query()->count())->toBe(0);
});

it('recusa estado desconhecido com 422', function () {
    $task = Task::factory()->for($this->project)->create();

    $this->patchJson(statusUrl($task), ['status' => 'doing'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('status');
});

it('desfaz a mudança de estado se o histórico falhar (rollback)', function () {
    $task = Task::factory()->for($this->project)->create();

    // Simula uma falha no meio da transação: gravar o histórico lança erro.
    TaskStatusHistory::creating(fn () => throw new RuntimeException('falha simulada'));

    $this->withoutExceptionHandling();
    expect(fn () => $this->patchJson(statusUrl($task), ['status' => 'done']))
        ->toThrow(RuntimeException::class);

    // Sem efeito parcial: o estado continua "todo".
    expect($task->refresh()->status->value)->toBe('todo')
        ->and($task->completed_at)->toBeNull();
});

it('desfaz a criação da task se o histórico inicial falhar (rollback)', function () {
    TaskStatusHistory::creating(fn () => throw new RuntimeException('falha simulada'));

    $this->withoutExceptionHandling();
    expect(fn () => $this->postJson("/api/projects/{$this->project->id}/tasks", ['title' => 'x']))
        ->toThrow(RuntimeException::class);

    expect(Task::query()->count())->toBe(0);
});

it('lista o histórico com o autor de cada transição', function () {
    $response = $this->postJson("/api/projects/{$this->project->id}/tasks", ['title' => 'Com histórico']);
    $task = Task::query()->findOrFail($response->json('data.id'));
    $this->patchJson(statusUrl($task), ['status' => 'done']);
    $this->patchJson(statusUrl($task), ['status' => 'todo']);

    $this->getJson("/api/projects/{$this->project->id}/tasks/{$task->id}/history")
        ->assertOk()
        ->assertJsonCount(3, 'data')
        ->assertJsonPath('data.0.from_status', null)
        ->assertJsonPath('data.1.to_status', 'done')
        ->assertJsonPath('data.2.to_status', 'todo')
        ->assertJsonPath('data.2.user.id', $this->user->id);
});
