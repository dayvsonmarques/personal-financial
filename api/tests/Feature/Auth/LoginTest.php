<?php

use App\Models\User;

beforeEach(function () {
    $this->user = actingAsMember();
    $this->user->update(['email' => 'joao@exemplo.test', 'password' => 'senha-forte-123']);
    auth()->logout();
});

it('faz login e retorna o usuário com a organização em /me (RF-03)', function () {
    $this->postJson('/api/v1/auth/login', ['email' => 'joao@exemplo.test', 'password' => 'senha-forte-123'])
        ->assertOk();

    $this->getJson('/api/v1/me')
        ->assertOk()
        ->assertJsonPath('data.email', 'joao@exemplo.test')
        ->assertJsonPath('data.organization.id', $this->user->current_organization_id)
        ->assertJsonPath('data.organization.currency', 'BRL')
        ->assertJsonMissingPath('data.password');
});

it('recusa credenciais inválidas', function () {
    $this->postJson('/api/v1/auth/login', ['email' => 'joao@exemplo.test', 'password' => 'errada'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('email');

    $this->assertGuest();
});

it('limita a 5 tentativas por minuto por e-mail e IP', function () {
    foreach (range(1, 5) as $_) {
        $this->postJson('/api/v1/auth/login', ['email' => 'joao@exemplo.test', 'password' => 'errada'])->assertUnprocessable();
    }

    $this->postJson('/api/v1/auth/login', ['email' => 'joao@exemplo.test', 'password' => 'errada'])->assertTooManyRequests();
});

it('faz logout', function () {
    $this->actingAs(User::query()->find($this->user->id));

    $this->postJson('/api/v1/auth/logout')->assertNoContent();

    $this->assertGuest();
});

it('exige autenticação em /me', function () {
    $this->getJson('/api/v1/me')->assertUnauthorized();
});
