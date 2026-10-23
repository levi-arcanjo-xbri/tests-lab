<?php

namespace App\Domain\Auth\Http\Controllers;

use App\Domain\Auth\Http\Resources\UserResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * GET /api/auth/me
 *
 * O frontend chama esta rota para saber quem está logado a partir do token.
 */
class MeController extends Controller
{
    /**
     * Devolve o usuário autenticado.
     */
    public function __invoke(Request $request): UserResource
    {
        return new UserResource($request->user());
    }
}
