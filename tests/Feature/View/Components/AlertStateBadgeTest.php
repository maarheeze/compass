<?php

declare(strict_types=1);

use Maarheeze\Compass\Alerts\AlertStatus;
use Maarheeze\Compass\Alerts\ConditionState;
use Maarheeze\Compass\Models\Alert;
use Maarheeze\Compass\View\BadgeType;
use Maarheeze\Compass\View\Components\AlertStateBadge;

test('badge type is neutral for a done alert', function (): void {
    $alert = Alert::factory()
        ->done()
        ->create();

    $alertStateBadge = $this->app->make(AlertStateBadge::class, ['alert' => $alert]);

    expect($alertStateBadge->badgeType())
        ->toBe(BadgeType::NEUTRAL);
});

test('badge type is danger for an active alert', function (): void {
    $alert = Alert::factory()
        ->active()
        ->create();

    $alertStateBadge = $this->app->make(AlertStateBadge::class, ['alert' => $alert]);

    expect($alertStateBadge->badgeType())
        ->toBe(BadgeType::DANGER);
});

test('badge type is success for a resolved alert', function (): void {
    $alert = Alert::factory()
        ->resolved()
        ->create();

    $alertStateBadge = $this->app->make(AlertStateBadge::class, ['alert' => $alert]);

    expect($alertStateBadge->badgeType())
        ->toBe(BadgeType::SUCCESS);
});

test('state is done for a done alert', function (): void {
    $alert = Alert::factory()
        ->done()
        ->create();

    $alertStateBadge = $this->app->make(AlertStateBadge::class, ['alert' => $alert]);

    expect($alertStateBadge->state())
        ->toBe(AlertStatus::DONE);
});

test('state is the condition of an open alert', function (): void {
    $alert = Alert::factory()
        ->active()
        ->create();

    $alertStateBadge = $this->app->make(AlertStateBadge::class, ['alert' => $alert]);

    expect($alertStateBadge->state())
        ->toBe(ConditionState::ACTIVE);
});

test('render renders the alert state badge view', function (): void {
    $alertStateBadge = $this->app->make(AlertStateBadge::class, ['alert' => Alert::factory()->create()]);

    expect($alertStateBadge->render()->name())
        ->toBe('compass::components.alert-state-badge');
});
