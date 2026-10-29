<?php

declare(strict_types=1);

use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\Alert;
use Maarheeze\Compass\View\Components\AlertReopenButton;

test('reopen url is the reopen route of the alert', function (): void {
    $alert = Alert::factory()->create();

    $alertReopenButton = $this->app->make(AlertReopenButton::class, ['alert' => $alert]);

    expect($alertReopenButton->reopenUrl)
        ->toBe(route(RouteName::ALERT_REOPEN, $alert));
});

test('should render for an alert that can be reopened', function (): void {
    $alert = Alert::factory()
        ->done()
        ->create();

    $alertReopenButton = $this->app->make(AlertReopenButton::class, ['alert' => $alert]);

    expect($alertReopenButton->shouldRender())
        ->toBeTrue();
});

test('should not render for an alert that cannot be reopened', function (): void {
    $alert = Alert::factory()
        ->resolved()
        ->create();

    $alertReopenButton = $this->app->make(AlertReopenButton::class, ['alert' => $alert]);

    expect($alertReopenButton->shouldRender())
        ->toBeFalse();
});

test('render renders the alert reopen button view', function (): void {
    $alert = Alert::factory()->create();

    $alertReopenButton = $this->app->make(AlertReopenButton::class, ['alert' => $alert]);

    expect($alertReopenButton->render()->name())
        ->toBe('compass::components.alert-reopen-button');
});
