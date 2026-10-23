<?php

namespace App\Domain\Auth\Actions;

use App\Domain\Auth\Models\User;

/**
 * Caso de uso: cadastrar um usuário e já devolver um token, para o cliente
 * entrar direto após o cadastro.
 *
 * A senha chega em texto puro e é transformada em hash pelo cast 'hashed'
 * do model User.
 */
class RegisterUserAction
{
    /**
     * @param  array{name: string, email: string, password: string}  $data
     * @return array{user: User, token: string}
     */
    public function execute(array $data, string $deviceName = 'api'): array
    {
        $user = User::query()->create($data);

        return [
            'user' => $user,
            'token' => $user->createToken($deviceName)->plainTextToken,
        ];
    }
}
