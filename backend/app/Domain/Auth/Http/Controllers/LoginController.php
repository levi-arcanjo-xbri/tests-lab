<?php

namespace App\Domain\Auth\Http\Controllers;

use App\Domain\Auth\Actions\IssueTokenAction;
use App\Domain\Auth\Http\Requests\LoginRequest;
use App\Domain\Auth\Http\Resources\UserResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

/**
 * POST /api/auth/login
 */
class LoginController extends Controller
{
    /**
     * Autentica o usuário e devolve um token Bearer.
     *
     * @unauthenticated
     */
    public function __invoke(LoginRequest $request, IssueTokenAction $action): JsonResponse
    {
        $result = $action->execute(
            $request->string('email')->toString(),
            $request->string('password')->toString(),
            $request->string('device_name', 'api')->toString(),
        );

        return response()->json([
            'token' => $result['token'],
            'token_type' => 'Bearer',
            'user' => new UserResource($result['user']),
        ]);
    }
}
