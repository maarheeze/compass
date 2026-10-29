<?php

declare(strict_types=1);

use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\Alert;
use Maarheeze\Compass\View\Components\AlertMuteButton;

test('mute url is the mute route of the alert', function (): void {
    $alert = Alert::factory()->create();

    $alertMuteButton = $this->app->make(AlertMuteButton::class, ['alert' => $alert]);

    expect($alertMuteButton->muteUrl)
        ->toBe(route(RouteName::ALERT_MUTE, $alert));
});

test('should render for an alert that can be muted', function (): void {
    $alert = Alert::factory()
        ->active()
        ->create();

    $alertMuteButton = $this->app->make(AlertMuteButton::class, ['alert' => $alert]);

    expect($alertMuteButton->shouldRender())
        ->toBeTrue();
});

test('should not render for an alert that cannot be muted', function (): void {
    $alert = Alert::factory()
        ->active()
        ->muted()
        ->create();

    $alertMuteButton = $this->app->make(AlertMuteButton::class, ['alert' => $alert]);

    expect($alertMuteButton->shouldRender())
        ->toBeFalse();
});

test('render renders the alert mute button view', function (): void {
    $alert = Alert::factory()->create();

    $alertMuteButton = $this->app->make(AlertMuteButton::class, ['alert' => $alert]);

    expect($alertMuteButton->render()->name())
        ->toBe('compass::components.alert-mute-button');
});
