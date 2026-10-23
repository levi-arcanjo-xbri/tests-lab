<?php

namespace App\Domain\Auth\Http\Controllers;

use App\Domain\Auth\Actions\RegisterUserAction;
use App\Domain\Auth\Http\Requests\RegisterRequest;
use App\Domain\Auth\Http\Resources\UserResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

/**
 * POST /api/auth/register
 */
class RegisterController extends Controller
{
    /**
     * Cadastra um usuário e devolve um token Bearer.
     *
     * @unauthenticated
     */
    public function __invoke(RegisterRequest $request, RegisterUserAction $action): JsonResponse
    {
        $data = $request->safe()->only(['name', 'email', 'password']);
        $result = $action->execute($data, $request->string('device_name', 'api')->toString());

        return response()->json([
            'token' => $result['token'],
            'token_type' => 'Bearer',
            'user' => new UserResource($result['user']),
        ], 201);
    }
}
