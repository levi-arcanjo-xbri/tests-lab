<?php

namespace App\Domain\Project\Http\Controllers;

use App\Domain\Project\Actions\ListProjectsAction;
use App\Domain\Project\Http\Requests\ListProjectsRequest;
use App\Domain\Project\Http\Resources\ProjectResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * GET /api/projects
 *
 * Padrão de todos os controllers do projeto: um controller invocável por
 * ação (__invoke). Ele só converte a entrada (FormRequest já validado),
 * chama a Action e devolve o Resource. Nenhuma regra de negócio aqui.
 *
 * As dependências (Request, Action) são injetadas pelo container do Laravel
 * a partir dos tipos dos parâmetros.
 */
class ListProjectsController extends Controller
{
    /**
     * Lista os projetos do usuário autenticado, com a contagem de tasks.
     */
    public function __invoke(ListProjectsRequest $request, ListProjectsAction $action): AnonymousResourceCollection
    {
        $projects = $action->execute($request->user(), $request->validated('search'));

        return ProjectResource::collection($projects);
    }
}
