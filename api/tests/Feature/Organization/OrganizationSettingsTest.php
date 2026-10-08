<?php

use Spatie\Activitylog\Models\Activity;

beforeEach(function () {
    $this->user = actingAsMember();
});

function validOrganizationPayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'Casa',
        'currency' => 'USD',
        'timezone' => 'America/Recife',
        'locale' => 'pt-BR',
    ], $overrides);
}

it('mostra a organização atual (RF-11)', function () {
    $this->getJson('/api/v1/organization')
        ->assertOk()
        ->assertJsonPath('data.id', $this->user->current_organization_id)
        ->assertJsonPath('data.currency', 'BRL')
        ->assertJsonPath('data.timezone', 'America/Sao_Paulo');
});

it('atualiza nome, moeda, fuso e locale', function () {
    $this->putJson('/api/v1/organization', validOrganizationPayload())
        ->assertOk()
        ->assertJsonPath('data.name', 'Casa')
        ->assertJsonPath('data.currency', 'USD')
        ->assertJsonPath('data.timezone', 'America/Recife');
});

it('valida moeda, fuso e locale', function (array $payload, string $field) {
    $this->putJson('/api/v1/organization', validOrganizationPayload($payload))
        ->assertUnprocessable()
        ->assertJsonValidationErrors($field);
})->with([
    'moeda inexistente' => [['currency' => 'XYZ'], 'currency'],
    'moeda minúscula' => [['currency' => 'brl'], 'currency'],
    'fuso inválido' => [['timezone' => 'Marte/Olympus'], 'timezone'],
    'locale não suportado' => [['locale' => 'en-US'], 'locale'],
    'nome vazio' => [['name' => ''], 'name'],
]);

it('registra a alteração no log de auditoria', function () {
    $this->putJson('/api/v1/organization', validOrganizationPayload())->assertOk();

    $log = Activity::query()->where('event', 'updated')->sole();

    expect($log->organization_id)->toBe($this->user->current_organization_id)
        ->and($log->attribute_changes['old']['currency'])->toBe('BRL')
        ->and($log->attribute_changes['attributes']['currency'])->toBe('USD');
});

it('exige e-mail verificado', function () {
    $this->user->forceFill(['email_verified_at' => null])->save();

    $this->getJson('/api/v1/organization')->assertForbidden();
});
