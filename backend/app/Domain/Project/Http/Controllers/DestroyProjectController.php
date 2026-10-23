<?php

namespace App\Domain\Project\Http\Controllers;

use App\Domain\Project\Actions\DeleteProjectAction;
use App\Domain\Project\Models\Project;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;

/**
 * DELETE /api/projects/{project}
 */
class DestroyProjectController extends Controller
{
    /**
     * Apaga um projeto e todas as suas tasks.
     */
    public function __invoke(Project $project, DeleteProjectAction $action): Response
    {
        $action->execute($project);

        // 204 No Content: sucesso, sem corpo de resposta.
        return response()->noContent();
    }
}
