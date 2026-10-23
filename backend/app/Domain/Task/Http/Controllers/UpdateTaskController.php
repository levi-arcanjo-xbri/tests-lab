<?php

namespace App\Domain\Task\Http\Controllers;

use App\Domain\Project\Models\Project;
use App\Domain\Task\Actions\UpdateTaskAction;
use App\Domain\Task\Http\Requests\UpdateTaskRequest;
use App\Domain\Task\Http\Resources\TaskResource;
use App\Domain\Task\Models\Task;
use App\Http\Controllers\Controller;

/**
 * PATCH /api/projects/{project}/tasks/{task}
 *
 * As rotas de task usam scopeBindings(): o Laravel busca a task DENTRO do
 * projeto da URL (`$project->tasks()->findOrFail($id)`). Uma task de outro
 * projeto dá 404, mesmo que o id exista.
 */
class UpdateTaskController extends Controller
{
    /**
     * Altera título e/ou descrição de uma task.
     */
    public function __invoke(UpdateTaskRequest $request, Project $project, Task $task, UpdateTaskAction $action): TaskResource
    {
        return new TaskResource($action->execute($task, $request->validated()));
    }
}
