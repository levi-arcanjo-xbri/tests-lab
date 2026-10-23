<?php

use App\Domain\Auth\Http\Requests\LoginRequest;

it('exige e-mail e senha no login', function () {
    $rules = (new LoginRequest)->rules();

    expect($rules)->toHaveKeys(['email', 'password'])
        ->and($rules['email'])->toContain('required', 'email')
        ->and($rules['password'])->toContain('required');
});
