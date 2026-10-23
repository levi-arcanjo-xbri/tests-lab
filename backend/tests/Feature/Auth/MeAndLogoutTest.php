<?php

use App\Domain\Auth\Models\User;

it('devolve o usuário dono do token Bearer', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test')->plainTextToken;

    $this->withToken($token)
        ->getJson('/api/auth/me')
        ->assertOk()
        ->assertJsonPath('data.id', $user->id);
});

it('recusa requisição sem token', function () {
    $this->getJson('/api/auth/me')->assertUnauthorized();
});

it('revoga o token no logout', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test')->plainTextToken;

    $this->withToken($token)->postJson('/api/auth/logout')->assertNoContent();

    expect($user->tokens()->count())->toBe(0);
});
