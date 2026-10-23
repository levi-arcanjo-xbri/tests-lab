<?php

namespace App\Domain\Project\Http\Controllers;

use App\Domain\Project\Actions\CreateProjectAction;
use App\Domain\Project\Http\Requests\StoreProjectRequest;
use App\Domain\Project\Http\Resources\ProjectResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

/**
 * POST /api/projects
 *
 * Aceita o cabeçalho opcional `Idempotency-Key` (middleware `idempotent`):
 * reenviar com a mesma chave devolve o mesmo projeto, sem criar outro.
 */
class StoreProjectController extends Controller
{
    /**
     * Cria um projeto.
     */
    public function __invoke(StoreProjectRequest $request, CreateProjectAction $action): JsonResponse
    {
        $project = $action->execute($request->user(), $request->validated());

        // 201 Created: o recurso foi criado.
        return (new ProjectResource($project))->response()->setStatusCode(201);
    }
}
