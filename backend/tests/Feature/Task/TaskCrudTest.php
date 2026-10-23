<?php

use App\Domain\Project\Models\Project;
use App\Domain\Task\Models\Task;
use App\Domain\Task\Models\TaskStatusHistory;

beforeEach(function () {
    $this->user = actingAsUser();
    $this->project = Project::factory()->for($this->user, 'owner')->create();
});

it('cria a task em "todo" com a primeira linha de histórico', function () {
    $response = $this->postJson("/api/projects/{$this->project->id}/tasks", [
        'title' => 'Escrever testes',
        'status' => 'done', // ignorado: o estado não vem do corpo
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.status', 'todo')
        ->assertJsonPath('data.allowed_transitions', ['done']);

    $history = TaskStatusHistory::query()->sole();
    expect($history->from_status)->toBeNull()
        ->and($history->to_status->value)->toBe('todo')
        ->and($history->user_id)->toBe($this->user->id);
});

it('valida o título', function () {
    $this->postJson("/api/projects/{$this->project->id}/tasks", ['title' => ''])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('title');
});

it('lista as tasks com pendentes primeiro e filtra por estado', function () {
    $done = Task::factory()->for($this->project)->done()->create();
    $todo = Task::factory()->for($this->project)->create();

    $this->getJson("/api/projects/{$this->project->id}/tasks")
        ->assertOk()
        ->assertJsonPath('data.0.id', $todo->id)
        ->assertJsonPath('data.1.id', $done->id);

    $this->getJson("/api/projects/{$this->project->id}/tasks?status=done")
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $done->id);

    $this->getJson("/api/projects/{$this->project->id}/tasks?status=invalido")
        ->assertUnprocessable();
});

it('altera o título sem mexer no estado', function () {
    $task = Task::factory()->for($this->project)->create();

    $this->patchJson("/api/projects/{$this->project->id}/tasks/{$task->id}", [
        'title' => 'Novo título',
        'status' => 'done',
    ])->assertOk()
        ->assertJsonPath('data.title', 'Novo título')
        ->assertJsonPath('data.status', 'todo');
});

it('apaga a task', function () {
    $task = Task::factory()->for($this->project)->create();

    $this->deleteJson("/api/projects/{$this->project->id}/tasks/{$task->id}")->assertNoContent();

    expect(Task::query()->count())->toBe(0);
});

it('não acessa task de outro projeto pela URL de um projeto próprio', function () {
    // A task existe, mas pertence a outro projeto: scopeBindings() devolve 404.
    $foreign = Task::factory()->create();

    $this->patchJson("/api/projects/{$this->project->id}/tasks/{$foreign->id}", ['title' => 'x'])
        ->assertNotFound();
});

it('não acessa tasks de projeto de outro usuário', function () {
    $other = Project::factory()->create();
    $task = Task::factory()->for($other)->create();

    $this->getJson("/api/projects/{$other->id}/tasks")->assertNotFound();
    $this->postJson("/api/projects/{$other->id}/tasks", ['title' => 'x'])->assertNotFound();
    $this->deleteJson("/api/projects/{$other->id}/tasks/{$task->id}")->assertNotFound();

    expect(Task::query()->count())->toBe(1);
});
