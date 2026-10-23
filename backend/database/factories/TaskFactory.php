<?php

namespace Database\Factories;

use App\Domain\Project\Models\Project;
use App\Domain\Task\Models\Task;
use App\Domain\Task\States\TaskStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Gera tasks falsas para testes e seed.
 *
 * Uso: Task::factory()->for($project)->done()->create();
 *
 * Atenção: a factory grava o estado direto, sem passar pela máquina de
 * estados e sem histórico. Serve para montar cenários de teste; o código de
 * produção usa sempre as Actions.
 *
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    protected $model = Task::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->optional()->paragraph(),
            'status' => TaskStatus::Todo,
            'completed_at' => null,
        ];
    }

    /**
     * Estado "concluída": precisa de completed_at por causa do CHECK no banco.
     */
    public function done(): static
    {
        return $this->state(fn () => [
            'status' => TaskStatus::Done,
            'completed_at' => now(),
        ]);
    }
}
