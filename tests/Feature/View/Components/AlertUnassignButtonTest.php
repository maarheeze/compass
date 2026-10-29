<?php

declare(strict_types=1);

use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\Alert;
use Maarheeze\Compass\Models\User;
use Maarheeze\Compass\View\Components\AlertUnassignButton;

test('unassign url is the unassign route of the alert', function (): void {
    $alert = Alert::factory()->create();

    $alertUnassignButton = $this->app->make(AlertUnassignButton::class, ['alert' => $alert]);

    expect($alertUnassignButton->unassignUrl)
        ->toBe(route(RouteName::ALERT_UNASSIGN, $alert));
});

test('should render for an alert that can be unassigned', function (): void {
    $alert = Alert::factory()
        ->active()
        ->for(User::factory(), 'assignee')
        ->create();

    $alertUnassignButton = $this->app->make(AlertUnassignButton::class, ['alert' => $alert]);

    expect($alertUnassignButton->shouldRender())
        ->toBeTrue();
});

test('should not render for an alert that cannot be unassigned', function (): void {
    $alert = Alert::factory()
        ->active()
        ->create(['assignee_id' => null]);

    $alertUnassignButton = $this->app->make(AlertUnassignButton::class, ['alert' => $alert]);

    expect($alertUnassignButton->shouldRender())
        ->toBeFalse();
});

test('render renders the alert unassign button view', function (): void {
    $alert = Alert::factory()->create();

    $alertUnassignButton = $this->app->make(AlertUnassignButton::class, ['alert' => $alert]);

    expect($alertUnassignButton->render()->name())
        ->toBe('compass::components.alert-unassign-button');
});
