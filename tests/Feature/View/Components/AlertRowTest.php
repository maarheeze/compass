<?php

declare(strict_types=1);

use Maarheeze\Compass\Alerts\ConditionState;
use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\Alert;
use Maarheeze\Compass\View\Components\AlertRow;

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

test('state since is when an active alert opened', function (): void {
    $alert = Alert::factory()->active()->create();

    $alertRow = $this->app->make(AlertRow::class, ['alert' => $alert]);

    expect($alertRow->stateSince()->toIso8601String())
        ->toBe($alert->created_at->toIso8601String());
});

test('state since is when a not applicable alert opened', function (): void {
    $alert = Alert::factory()->active()->create(['condition' => ConditionState::NOT_APPLICABLE]);

    $alertRow = $this->app->make(AlertRow::class, ['alert' => $alert]);

    expect($alertRow->stateSince()->toIso8601String())
        ->toBe($alert->created_at->toIso8601String());
});

test('state since is when a resolved alert resolved', function (): void {
    $alert = Alert::factory()->resolved()->create();

    $alertRow = $this->app->make(AlertRow::class, ['alert' => $alert]);

    expect($alertRow->stateSince()->toIso8601String())
        ->toBe($alert->resolved_at?->toIso8601String());
});

test('state since is when a done alert was marked done', function (): void {
    $alert = Alert::factory()->done()->create();

    $alertRow = $this->app->make(AlertRow::class, ['alert' => $alert]);

    expect($alertRow->stateSince()->toIso8601String())
        ->toBe($alert->done_at?->toIso8601String());
});
