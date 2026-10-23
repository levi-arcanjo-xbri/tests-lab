<?php

namespace App\Domain\Task\Http\Controllers;

use App\Domain\Project\Models\Project;
use App\Domain\Task\Actions\ListTasksAction;
use App\Domain\Task\Http\Requests\ListTasksRequest;
use App\Domain\Task\Http\Resources\TaskResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * GET /api/projects/{project}/tasks?status=todo|done
 */
class ListTasksController extends Controller
{
    /**
     * Lista as tasks de um projeto.
     */
    public function __invoke(ListTasksRequest $request, Project $project, ListTasksAction $action): AnonymousResourceCollection
    {
        return TaskResource::collection($action->execute($project, $request->status()));
    }
}
