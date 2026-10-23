<?php

namespace App\Domain\Project\Actions;

use App\Domain\Auth\Models\User;
use App\Domain\Project\Models\Project;

/**
 * Caso de uso: criar um projeto para o usuário autenticado.
 *
 * Criar pela relação (`$owner->projects()->create()`) preenche o `user_id`
 * a partir do dono, e não do corpo da requisição.
 */
class CreateProjectAction
{
    /**
     * @param  array{name: string, description?: string|null}  $data
     */
    public function execute(User $owner, array $data): Project
    {
        $project = $owner->projects()->create($data);

        // Um projeto recém-criado não tem tasks; evita outra consulta só para contar.
        $project->setAttribute('tasks_count', 0);
        $project->setAttribute('done_tasks_count', 0);

        return $project;
    }
}
