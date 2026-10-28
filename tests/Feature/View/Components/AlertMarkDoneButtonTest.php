<?php

declare(strict_types=1);

use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\Alert;
use Maarheeze\Compass\View\Components\AlertMarkDoneButton;

test('mark done url is the mark done route of the alert', function (): void {
    $alert = Alert::factory()->create();

    $alertMarkDoneButton = $this->app->make(AlertMarkDoneButton::class, ['alert' => $alert]);

    expect($alertMarkDoneButton->markDoneUrl)
        ->toBe(route(RouteName::ALERT_MARK_DONE, $alert));
});

test('should render for an alert that can be marked done', function (): void {
    $alert = Alert::factory()
        ->resolved()
        ->create();

    $alertMarkDoneButton = $this->app->make(AlertMarkDoneButton::class, ['alert' => $alert]);

    expect($alertMarkDoneButton->shouldRender())
        ->toBeTrue();
});

test('should not render for an alert that cannot be marked done', function (): void {
    $alert = Alert::factory()
        ->active()
        ->create();

    $alertMarkDoneButton = $this->app->make(AlertMarkDoneButton::class, ['alert' => $alert]);

    expect($alertMarkDoneButton->shouldRender())
        ->toBeFalse();
});

test('render renders the alert mark done button view', function (): void {
    $alert = Alert::factory()->create();

    $alertMarkDoneButton = $this->app->make(AlertMarkDoneButton::class, ['alert' => $alert]);

    expect($alertMarkDoneButton->render()->name())
        ->toBe('compass::components.alert-mark-done-button');
});
