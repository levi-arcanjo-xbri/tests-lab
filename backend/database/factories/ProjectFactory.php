<?php

namespace Database\Factories;

use App\Domain\Auth\Models\User;
use App\Domain\Project\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Gera projetos falsos para testes e seed.
 *
 * Uso: Project::factory()->for($user, 'owner')->create();
 *
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    protected $model = Project::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // Cria um dono novo se o teste não informar um.
            'user_id' => User::factory(),
            'name' => fake()->unique()->words(3, true),
            'description' => fake()->optional()->sentence(),
        ];
    }
}
