<?php

use App\Exceptions\MissingCurrentOrganization;
use App\Models\Organization;
use App\Support\CurrentOrganization;
use Tests\Support\Widget;

beforeEach(function () {
    Widget::createTable();
    $this->orgA = Organization::factory()->create();
    $this->orgB = Organization::factory()->create();
});

it('preenche organization_id com a organização atual ao criar', function () {
    app(CurrentOrganization::class)->set($this->orgA);

    $widget = Widget::create(['name' => 'x']);

    expect($widget->organization_id)->toBe($this->orgA->id);
});

it('não enxerga registros de outra organização', function () {
    app(CurrentOrganization::class)->set($this->orgB);
    $deB = Widget::create(['name' => 'de B']);

    app(CurrentOrganization::class)->set($this->orgA);
    Widget::create(['name' => 'de A']);

    expect(Widget::pluck('name')->all())->toBe(['de A'])
        ->and(Widget::find($deB->id))->toBeNull();
});

it('falha ao consultar sem organização atual', function () {
    Widget::query()->get();
})->throws(MissingCurrentOrganization::class);
