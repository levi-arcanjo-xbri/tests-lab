<?php

namespace App\Domain\Task\Http\Controllers;

use App\Domain\Project\Models\Project;
use App\Domain\Task\Actions\ListTaskHistoryAction;
use App\Domain\Task\Http\Resources\TaskStatusHistoryResource;
use App\Domain\Task\Models\Task;
use App\Http\Controllers\Controller;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * GET /api/projects/{project}/tasks/{task}/history
 */
class ListTaskHistoryController extends Controller
{
    /**
     * Lista o histórico de estados de uma task.
     */
    public function __invoke(Project $project, Task $task, ListTaskHistoryAction $action): AnonymousResourceCollection
    {
        return TaskStatusHistoryResource::collection($action->execute($task));
    }
}
