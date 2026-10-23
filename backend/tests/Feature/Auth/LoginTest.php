<?php

use App\Domain\Auth\Models\User;

it('emite um token com credenciais válidas', function () {
    $user = User::factory()->create(['password' => 'secret-123']);

    $response = $this->postJson('/api/auth/login', [
        'email' => $user->email,
        'password' => 'secret-123',
    ]);

    $response->assertOk()
        ->assertJsonPath('token_type', 'Bearer')
        ->assertJsonPath('user.id', $user->id)
        ->assertJsonStructure(['token', 'token_type', 'user' => ['id', 'name', 'email']]);

    expect($user->tokens()->count())->toBe(1);
});

it('recusa credenciais inválidas sem emitir token', function () {
    $user = User::factory()->create(['password' => 'secret-123']);

    $this->postJson('/api/auth/login', [
        'email' => $user->email,
        'password' => 'errada',
    ])->assertUnprocessable()->assertJsonValidationErrors('email');

    expect($user->tokens()->count())->toBe(0);
});

it('valida os campos obrigatórios', function () {
    $this->postJson('/api/auth/login', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['email', 'password']);
});

it('recusa e-mail inexistente', function () {
    $this->postJson('/api/auth/login', [
        'email' => 'ninguem@example.com',
        'password' => 'qualquer',
    ])->assertUnprocessable()->assertJsonValidationErrors('email');
});

it('limita as tentativas de login por e-mail', function () {
    config(['auth.throttle_per_minute' => 2]);
    $payload = ['email' => 'alvo@example.com', 'password' => 'x'];

    $this->postJson('/api/auth/login', $payload)->assertUnprocessable();
    $this->postJson('/api/auth/login', $payload)->assertUnprocessable();
    $this->postJson('/api/auth/login', $payload)->assertTooManyRequests();

    // Outro e-mail no mesmo IP continua liberado.
    $this->postJson('/api/auth/login', ['email' => 'outro@example.com', 'password' => 'x'])
        ->assertUnprocessable();
});
