<?php

use Illuminate\Support\Facades\Route;
use Tests\Support\Widget;

beforeEach(function () {
    Widget::createTable();

    Route::middleware(['api', 'auth:sanctum', 'org'])
        ->get('/api/v1/_test/widgets/{id}', fn (int $id) => Widget::findOrFail($id));
});

it('retorna o recurso para membros da organização dona', function () {
    actingAsMember();
    $widget = Widget::create(['name' => 'meu']);

    $this->getJson("/api/v1/_test/widgets/{$widget->id}")->assertOk()->assertJsonPath('name', 'meu');
});

it('retorna 404 para usuário de outra organização (CA-01)', function () {
    actingAsMember();
    $widget = Widget::create(['name' => 'alheio']);

    expectCrossTenant404('GET', "/api/v1/_test/widgets/{$widget->id}");
});

it('exige autenticação', function () {
    $this->getJson('/api/v1/_test/widgets/1')->assertUnauthorized();
});
