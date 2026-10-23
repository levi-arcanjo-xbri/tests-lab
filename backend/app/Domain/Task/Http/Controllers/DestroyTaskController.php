<?php

namespace App\Domain\Task\Http\Controllers;

use App\Domain\Project\Models\Project;
use App\Domain\Task\Actions\DeleteTaskAction;
use App\Domain\Task\Models\Task;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;

/**
 * DELETE /api/projects/{project}/tasks/{task}
 */
class DestroyTaskController extends Controller
{
    /**
     * Apaga uma task.
     */
    public function __invoke(Project $project, Task $task, DeleteTaskAction $action): Response
    {
        $action->execute($task);

        return response()->noContent();
    }
}
