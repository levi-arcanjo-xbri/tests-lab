<?php

namespace App\Domain\Project\Actions;

use App\Domain\Project\Models\Project;

/**
 * Caso de uso: alterar nome e descrição de um projeto.
 *
 * A permissão (só o dono) já foi verificada pela policy na rota.
 */
class UpdateProjectAction
{
    /**
     * @param  array{name?: string, description?: string|null}  $data
     */
    public function execute(Project $project, array $data): Project
    {
        $project->update($data);

        return $project->loadCount(Project::taskCounts());
    }
}
