<?php

use App\Models\OrganizationMember;
use App\Models\User;

function registerPayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'Maria Silva',
        'email' => 'maria@exemplo.test',
        'password' => 'senha-forte-123',
        'password_confirmation' => 'senha-forte-123',
    ], $overrides);
}

it('cadastra o usuário com a organização pessoal (RF-01, RF-10)', function () {
    $this->postJson('/api/v1/auth/register', registerPayload())->assertCreated();

    $user = User::query()->where('email', 'maria@exemplo.test')->sole();
    $organization = $user->currentOrganization;

    expect($user->password)->toStartWith('$argon2id$')
        ->and($organization->name)->toBe('Finanças de Maria')
        ->and($organization->currency)->toBe('BRL')
        ->and($organization->timezone)->toBe('America/Sao_Paulo')
        ->and($organization->locale)->toBe('pt-BR')
        ->and($organization->members()->sole()->pivot->role)->toBe(OrganizationMember::ROLE_OWNER);

    $this->assertAuthenticatedAs($user);
});

it('normaliza o e-mail em minúsculas', function () {
    $this->postJson('/api/v1/auth/register', registerPayload(['email' => 'Maria@Exemplo.TEST']))->assertCreated();

    expect(User::query()->sole()->email)->toBe('maria@exemplo.test');
});

it('recusa e-mail já cadastrado', function () {
    User::factory()->create(['email' => 'maria@exemplo.test']);

    $this->postJson('/api/v1/auth/register', registerPayload())
        ->assertUnprocessable()
        ->assertJsonValidationErrors('email');
});

it('exige senha com no mínimo 8 caracteres e confirmação', function (array $payload) {
    $this->postJson('/api/v1/auth/register', registerPayload($payload))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('password');
})->with([
    'curta' => [['password' => '1234567', 'password_confirmation' => '1234567']],
    'sem confirmação' => [['password_confirmation' => 'outra-senha-123']],
]);

it('exige nome com até 120 caracteres', function () {
    $this->postJson('/api/v1/auth/register', registerPayload(['name' => str_repeat('a', 121)]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('name');
});
