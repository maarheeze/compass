<?php

declare(strict_types=1);

use Maarheeze\Compass\Alerts\AlertCheckState;
use Maarheeze\Compass\Alerts\ConditionState;
use Maarheeze\Compass\Models\AlertCheck;
use Maarheeze\Compass\View\BadgeType;
use Maarheeze\Compass\View\Components\AlertCheckStateBadge;

test('badge type is danger for a failing check', function (): void {
    $alertCheck = AlertCheck::factory()->make(['condition' => ConditionState::ACTIVE]);
    $alertCheck->alert_is_muted = false;

    $alertCheckStateBadge = $this->app->make(AlertCheckStateBadge::class, ['alertCheck' => $alertCheck]);

    expect($alertCheckStateBadge->badgeType())
        ->toBe(BadgeType::DANGER);
});

test('badge type is neutral for a muted check', function (): void {
    $alertCheck = AlertCheck::factory()->make(['condition' => ConditionState::ACTIVE]);
    $alertCheck->alert_is_muted = true;

    $alertCheckStateBadge = $this->app->make(AlertCheckStateBadge::class, ['alertCheck' => $alertCheck]);

    expect($alertCheckStateBadge->badgeType())
        ->toBe(BadgeType::NEUTRAL);
});

test('badge type is success for an ok check', function (): void {
    $alertCheck = AlertCheck::factory()->make(['condition' => ConditionState::RESOLVED]);

    $alertCheckStateBadge = $this->app->make(AlertCheckStateBadge::class, ['alertCheck' => $alertCheck]);

    expect($alertCheckStateBadge->badgeType())
        ->toBe(BadgeType::SUCCESS);
});

test('state is the state of the check', function (): void {
    $alertCheck = AlertCheck::factory()->make(['condition' => ConditionState::RESOLVED]);

    $alertCheckStateBadge = $this->app->make(AlertCheckStateBadge::class, ['alertCheck' => $alertCheck]);

    expect($alertCheckStateBadge->state())
        ->toBe(AlertCheckState::OK);
});

test('render renders the alert check state badge view', function (): void {
    $alertCheck = AlertCheck::factory()->make(['condition' => ConditionState::RESOLVED]);

    $alertCheckStateBadge = $this->app->make(AlertCheckStateBadge::class, ['alertCheck' => $alertCheck]);

    expect($alertCheckStateBadge->render()->name())
        ->toBe('compass::components.alert-check-state-badge');
});
