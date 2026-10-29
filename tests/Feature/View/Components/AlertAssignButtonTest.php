<?php

declare(strict_types=1);

use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\Alert;
use Maarheeze\Compass\Models\User;
use Maarheeze\Compass\Providers\AuthenticationProvider;
use Maarheeze\Compass\View\Components\AlertAssignButton;

test('assign url is the assign route of the alert', function (): void {
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);
    $alert = Alert::factory()->create();

    $alertAssignButton = $this->app->make(AlertAssignButton::class, ['alert' => $alert]);

    expect($alertAssignButton->assignUrl)
        ->toBe(route(RouteName::ALERT_ASSIGN, $alert));
});

test('should render for an alert the signed in user can assign to themselves', function (): void {
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);
    $alert = Alert::factory()
        ->active()
        ->create(['assignee_id' => null]);

    $alertAssignButton = $this->app->make(AlertAssignButton::class, ['alert' => $alert]);

    expect($alertAssignButton->shouldRender())
        ->toBeTrue();
});

test('should not render for an alert assigned to the signed in user', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user, AuthenticationProvider::GUARD);
    $alert = Alert::factory()
        ->active()
        ->for($user, 'assignee')
        ->create();

    $alertAssignButton = $this->app->make(AlertAssignButton::class, ['alert' => $alert]);

    expect($alertAssignButton->shouldRender())
        ->toBeFalse();
});

test('render renders the alert assign button view', function (): void {
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);
    $alert = Alert::factory()->create();

    $alertAssignButton = $this->app->make(AlertAssignButton::class, ['alert' => $alert]);

    expect($alertAssignButton->render()->name())
        ->toBe('compass::components.alert-assign-button');
});
