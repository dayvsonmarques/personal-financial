<?php

use Spatie\Activitylog\Models\Activity;
use Tests\Support\AuditedWidget;
use Tests\Support\Widget;

beforeEach(fn () => Widget::createTable());

it('registra criação, edição e exclusão com organização, autor e IP', function () {
    $user = actingAsMember();

    $widget = AuditedWidget::create(['name' => 'antes']);
    $widget->update(['name' => 'depois']);
    $widget->delete();

    $logs = Activity::query()->orderBy('id')->get();

    expect($logs->pluck('event')->all())->toBe(['created', 'updated', 'deleted'])
        ->and($logs->pluck('organization_id')->unique()->all())->toBe([$user->current_organization_id])
        ->and($logs->pluck('causer_id')->unique()->all())->toBe([$user->id])
        ->and($logs->first()->ip)->toBe('127.0.0.1');

    $update = $logs[1];
    expect($update->attribute_changes['old']['name'])->toBe('antes')
        ->and($update->attribute_changes['attributes']['name'])->toBe('depois');
});

it('registra apenas atributos alterados e ignora updated_at', function () {
    actingAsMember();

    $widget = AuditedWidget::create(['name' => 'a']);
    $widget->update(['name' => 'b']);

    $changes = Activity::query()->where('event', 'updated')->sole()->attribute_changes;

    expect(array_keys($changes['attributes']))->toBe(['name']);
});

it('nunca registra campos sensíveis', function () {
    actingAsMember();

    activity()->withProperties(['password' => 'segredo', 'token' => 'x', 'ok' => 1])->log('teste');

    expect(Activity::query()->sole()->properties->all())->toBe(['ok' => 1]);
});
