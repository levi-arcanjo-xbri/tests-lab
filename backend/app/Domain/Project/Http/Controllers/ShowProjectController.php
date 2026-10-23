<?php

namespace App\Domain\Project\Http\Controllers;

use App\Domain\Project\Http\Resources\ProjectResource;
use App\Domain\Project\Models\Project;
use App\Http\Controllers\Controller;

/**
 * GET /api/projects/{project}
 *
 * O parâmetro `Project $project` usa route model binding: o Laravel busca o
 * projeto pelo id da URL (404 se não existir). A policy (middleware
 * `can:view,project`) já garantiu que o usuário é o dono.
 */
class ShowProjectController extends Controller
{
    /**
     * Mostra um projeto.
     */
    public function __invoke(Project $project): ProjectResource
    {
        return new ProjectResource($project->loadCount(Project::taskCounts()));
    }
}
