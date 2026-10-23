<?php

use App\Domain\Auth\Models\User;
use Illuminate\Support\Facades\Hash;

it('cadastra o usuário e devolve um token', function () {
    $response = $this->postJson('/api/auth/register', [
        'name' => 'Maria',
        'email' => 'maria@example.com',
        'password' => 'senha-segura',
        'password_confirmation' => 'senha-segura',
    ]);

    $response->assertCreated()
        ->assertJsonPath('token_type', 'Bearer')
        ->assertJsonPath('user.email', 'maria@example.com');

    $user = User::query()->where('email', 'maria@example.com')->sole();
    // A senha é gravada com hash, nunca em texto puro.
    expect(Hash::check('senha-segura', $user->password))->toBeTrue()
        ->and($user->password)->not->toBe('senha-segura');

    // O token devolvido funciona nas rotas protegidas.
    $this->withToken($response->json('token'))->getJson('/api/auth/me')->assertOk();
});

it('recusa e-mail já cadastrado e senha sem confirmação', function () {
    User::factory()->create(['email' => 'maria@example.com']);

    $this->postJson('/api/auth/register', [
        'name' => 'Maria',
        'email' => 'maria@example.com',
        'password' => 'senha-segura',
    ])->assertUnprocessable()->assertJsonValidationErrors(['email', 'password']);
});
