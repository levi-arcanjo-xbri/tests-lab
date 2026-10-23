<?php

namespace App\Domain\Project\Http\Controllers;

use App\Domain\Project\Actions\UpdateProjectAction;
use App\Domain\Project\Http\Requests\UpdateProjectRequest;
use App\Domain\Project\Http\Resources\ProjectResource;
use App\Domain\Project\Models\Project;
use App\Http\Controllers\Controller;

/**
 * PATCH /api/projects/{project}
 */
class UpdateProjectController extends Controller
{
    /**
     * Altera nome e/ou descrição de um projeto.
     */
    public function __invoke(UpdateProjectRequest $request, Project $project, UpdateProjectAction $action): ProjectResource
    {
        return new ProjectResource($action->execute($project, $request->validated()));
    }
}
