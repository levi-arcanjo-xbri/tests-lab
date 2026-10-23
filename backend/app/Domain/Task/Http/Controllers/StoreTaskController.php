<?php

namespace App\Domain\Task\Http\Controllers;

use App\Domain\Project\Models\Project;
use App\Domain\Task\Actions\CreateTaskAction;
use App\Domain\Task\Http\Requests\StoreTaskRequest;
use App\Domain\Task\Http\Resources\TaskResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

/**
 * POST /api/projects/{project}/tasks
 *
 * Aceita `Idempotency-Key`: o reenvio com a mesma chave não duplica a task.
 */
class StoreTaskController extends Controller
{
    /**
     * Cria uma task no projeto, no estado "todo".
     */
    public function __invoke(StoreTaskRequest $request, Project $project, CreateTaskAction $action): JsonResponse
    {
        $task = $action->execute($project, $request->user(), $request->validated());

        return (new TaskResource($task))->response()->setStatusCode(201);
    }
}
