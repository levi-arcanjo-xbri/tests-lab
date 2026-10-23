<?php

namespace App\Domain\Project\Actions;

use App\Domain\Project\Models\Project;

/**
 * Caso de uso: apagar um projeto.
 *
 * As tasks e o histórico delas são apagados pelo próprio banco
 * (chaves estrangeiras com ON DELETE CASCADE), numa única instrução.
 */
class DeleteProjectAction
{
    public function execute(Project $project): void
    {
        $project->delete();
    }
}
