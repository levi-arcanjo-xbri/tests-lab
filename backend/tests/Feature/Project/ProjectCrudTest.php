<?php

/**
 * Testes de feature da API de projetos: requisição HTTP de ponta a ponta,
 * passando por rota, middleware, policy, FormRequest, Action e banco real.
 */

use App\Domain\Auth\Models\User;
use App\Domain\Project\Models\Project;
use App\Domain\Task\Models\Task;

it('exige autenticação', function () {
    $this->getJson('/api/projects')->assertUnauthorized();
});

it('lista só os projetos do usuário, com contagem de tasks', function () {
    $user = actingAsUser();
    $mine = Project::factory()->for($user, 'owner')->create();
    Task::factory()->for($mine)->count(2)->create();
    Task::factory()->for($mine)->done()->create();
    Project::factory()->create(); // de outro usuário

    $this->getJson('/api/projects')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $mine->id)
        ->assertJsonPath('data.0.tasks_count', 3)
        ->assertJsonPath('data.0.done_tasks_count', 1);
});

it('filtra a listagem pelo nome', function () {
    $user = actingAsUser();
    Project::factory()->for($user, 'owner')->create(['name' => 'Casa nova']);
    Project::factory()->for($user, 'owner')->create(['name' => 'Trabalho']);

    $this->getJson('/api/projects?search=casa')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'Casa nova');
});

it('cria um projeto para o usuário autenticado', function () {
    $user = actingAsUser();

    $this->postJson('/api/projects', ['name' => 'Novo', 'description' => 'Desc', 'user_id' => 999])
        ->assertCreated()
        ->assertJsonPath('data.name', 'Novo')
        ->assertJsonPath('data.tasks_count', 0);

    // O dono vem do token, não do corpo: o user_id enviado foi ignorado.
    expect(Project::query()->sole()->user_id)->toBe($user->id);
});

it('recusa nome repetido para o mesmo dono, mas aceita para outro dono', function () {
    $user = actingAsUser();
    Project::factory()->for($user, 'owner')->create(['name' => 'Repetido']);
    Project::factory()->create(['name' => 'Outro dono']);

    $this->postJson('/api/projects', ['name' => 'Repetido'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('name');

    $this->postJson('/api/projects', ['name' => 'Outro dono'])->assertCreated();
});

it('mostra, altera e apaga o próprio projeto', function () {
    $user = actingAsUser();
    $project = Project::factory()->for($user, 'owner')->create(['name' => 'Antes']);
    Task::factory()->for($project)->create();

    $this->getJson("/api/projects/{$project->id}")->assertOk()->assertJsonPath('data.tasks_count', 1);

    $this->patchJson("/api/projects/{$project->id}", ['name' => 'Depois'])
        ->assertOk()
        ->assertJsonPath('data.name', 'Depois');

    $this->deleteJson("/api/projects/{$project->id}")->assertNoContent();

    // As tasks foram apagadas em cascata pelo banco.
    expect(Project::query()->count())->toBe(0)
        ->and(Task::query()->count())->toBe(0);
});

it('responde 404 para projeto de outro usuário', function (string $method) {
    actingAsUser();
    $other = Project::factory()->for(User::factory(), 'owner')->create();

    $this->json($method, "/api/projects/{$other->id}", ['name' => 'x'])->assertNotFound();

    expect($other->fresh())->not->toBeNull()
        ->and($other->fresh()->name)->toBe($other->name);
})->with(['GET', 'PATCH', 'DELETE']);
