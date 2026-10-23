<?php

namespace App\Domain\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * POST /api/auth/logout
 *
 * Apaga só o token usado nesta requisição; outras sessões continuam válidas.
 */
class LogoutController extends Controller
{
    /**
     * Revoga o token usado na requisição.
     */
    public function __invoke(Request $request): Response
    {
        $request->user()->currentAccessToken()->delete();

        return response()->noContent();
    }
}
