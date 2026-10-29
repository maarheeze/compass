<?php

declare(strict_types=1);

use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\Alert;
use Maarheeze\Compass\View\Components\AlertUnmuteButton;

test('unmute url is the unmute route of the alert', function (): void {
    $alert = Alert::factory()->create();

    $alertUnmuteButton = $this->app->make(AlertUnmuteButton::class, ['alert' => $alert]);

    expect($alertUnmuteButton->unmuteUrl)
        ->toBe(route(RouteName::ALERT_UNMUTE, $alert));
});

test('should render for an alert that can be unmuted', function (): void {
    $alert = Alert::factory()
        ->active()
        ->muted()
        ->create();

    $alertUnmuteButton = $this->app->make(AlertUnmuteButton::class, ['alert' => $alert]);

    expect($alertUnmuteButton->shouldRender())
        ->toBeTrue();
});

test('should not render for an alert that cannot be unmuted', function (): void {
    $alert = Alert::factory()
        ->active()
        ->create();

    $alertUnmuteButton = $this->app->make(AlertUnmuteButton::class, ['alert' => $alert]);

    expect($alertUnmuteButton->shouldRender())
        ->toBeFalse();
});

test('render renders the alert unmute button view', function (): void {
    $alert = Alert::factory()->create();

    $alertUnmuteButton = $this->app->make(AlertUnmuteButton::class, ['alert' => $alert]);

    expect($alertUnmuteButton->render()->name())
        ->toBe('compass::components.alert-unmute-button');
});
