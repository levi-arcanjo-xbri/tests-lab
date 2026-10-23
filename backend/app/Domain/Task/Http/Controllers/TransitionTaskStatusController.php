<?php

namespace App\Domain\Task\Http\Controllers;

use App\Domain\Project\Models\Project;
use App\Domain\Task\Actions\TransitionTaskStatusAction;
use App\Domain\Task\Http\Requests\TransitionTaskStatusRequest;
use App\Domain\Task\Http\Resources\TaskResource;
use App\Domain\Task\Models\Task;
use App\Http\Controllers\Controller;

/**
 * PATCH /api/projects/{project}/tasks/{task}/status   { "status": "done" }
 *
 * Respostas:
 * - 200 com a task atualizada;
 * - 409 se a máquina de estados recusar a transição (ex.: done → done);
 * - 422 se o status não for um valor conhecido.
 */
class TransitionTaskStatusController extends Controller
{
    /**
     * Muda o estado de uma task (concluir ou reabrir).
     */
    public function __invoke(
        TransitionTaskStatusRequest $request,
        Project $project,
        Task $task,
        TransitionTaskStatusAction $action,
    ): TaskResource {
        return new TaskResource($action->execute($task, $request->targetStatus(), $request->user()));
    }
}
