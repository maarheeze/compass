<?php

declare(strict_types=1);

use Maarheeze\Compass\Alerts\AlertTypeDefinitions;
use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\Alert;
use Maarheeze\Compass\View\Components\AlertRow;

test('description is the description of its type', function (): void {
    $alert = Alert::factory()->create();
    $alertTypeDefinitions = $this->app->make(AlertTypeDefinitions::class);

    $alertRow = $this->app->make(AlertRow::class, ['alert' => $alert]);

    expect($alertRow->description)
        ->toBe($alertTypeDefinitions->definition($alert->type)->description($alert));
});

test('render renders the alert row view', function (): void {
    $alert = Alert::factory()->create();

    $alertRow = $this->app->make(AlertRow::class, ['alert' => $alert]);

    expect($alertRow->render()->name())
        ->toBe('compass::components.alert-row');
});

test('url is the compass page of the alert', function (): void {
    $alert = Alert::factory()->create();

    $alertRow = $this->app->make(AlertRow::class, ['alert' => $alert]);

    expect($alertRow->url)
        ->toBe(route(RouteName::ALERT, $alert));
});
