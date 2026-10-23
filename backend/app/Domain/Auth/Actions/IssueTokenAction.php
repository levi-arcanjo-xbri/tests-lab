<?php

namespace App\Domain\Auth\Actions;

use App\Domain\Auth\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * Caso de uso: login. Confere e-mail e senha e emite um token pessoal do
 * Sanctum, que o cliente envia depois como `Authorization: Bearer <token>`.
 *
 * Em caso de falha, a mesma mensagem serve para e-mail inexistente e senha
 * errada, para não revelar quais e-mails estão cadastrados.
 */
class IssueTokenAction
{
    /**
     * Valida as credenciais e emite um token pessoal do Sanctum.
     *
     * @return array{user: User, token: string}
     */
    public function execute(string $email, string $password, string $deviceName = 'api'): array
    {
        $user = User::query()->where('email', $email)->first();

        if ($user === null || ! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        return [
            'user' => $user,
            'token' => $user->createToken($deviceName)->plainTextToken,
        ];
    }
}
