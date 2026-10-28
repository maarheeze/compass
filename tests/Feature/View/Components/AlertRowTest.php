<?php

declare(strict_types=1);

use Maarheeze\Compass\Alerts\AlertTypeDefinitions;
use Maarheeze\Compass\Models\Alert;
use Maarheeze\Compass\View\Components\AlertRow;

test('description is the description of its type', function (): void {
    $alert = Alert::factory()->create();
    $alertTypeDefinitions = $this->app->make(AlertTypeDefinitions::class);

    $alertRow = $this->app->make(AlertRow::class, ['alert' => $alert]);

    expect($alertRow->description)
        ->toBe($alertTypeDefinitions->definition($alert->type)->description($alert));
});

test('is condition present is true for a present alert', function (): void {
    $alert = Alert::factory()
        ->present()
        ->create();

    $alertRow = $this->app->make(AlertRow::class, ['alert' => $alert]);

    expect($alertRow->isConditionPresent)
        ->toBeTrue();
});

test('is condition present is false for a gone alert', function (): void {
    $alert = Alert::factory()
        ->gone()
        ->create();

    $alertRow = $this->app->make(AlertRow::class, ['alert' => $alert]);

    expect($alertRow->isConditionPresent)
        ->toBeFalse();
});

test('render renders the alert row view', function (): void {
    $alert = Alert::factory()->create();

    $alertRow = $this->app->make(AlertRow::class, ['alert' => $alert]);

    expect($alertRow->render()->name())
        ->toBe('compass::components.alert-row');
});
