<?php

declare(strict_types=1);

use Maarheeze\Compass\Alerts\AlertCheckState;
use Maarheeze\Compass\Alerts\AlertCheckStateCount;
use Maarheeze\Compass\View\AlertCheckStateBadgeType;
use Maarheeze\Compass\View\Components\AlertCheckStateCountBadge;

test('badge type is the badge type of the state', function (): void {
    $alertCheckState = fake()->randomElement(AlertCheckState::cases());

    $alertCheckStateCountBadge = $this->app->make(AlertCheckStateCountBadge::class, [
        'alertCheckStateCount' => new AlertCheckStateCount($alertCheckState, fake()->numberBetween(1, 10)),
    ]);

    expect($alertCheckStateCountBadge->badgeType)
        ->toBe(new AlertCheckStateBadgeType()->badgeType($alertCheckState));
});

test('label is the translated count of the state', function (): void {
    $alertCheckStateCountBadge = $this->app->make(AlertCheckStateCountBadge::class, [
        'alertCheckStateCount' => new AlertCheckStateCount(AlertCheckState::FAILING, 2),
    ]);

    expect($alertCheckStateCountBadge->label)
        ->toBe('2 failing');
});

test('label counts the checks of every state', function (AlertCheckState $alertCheckState): void {
    $alertCheckStateCountBadge = $this->app->make(AlertCheckStateCountBadge::class, [
        'alertCheckStateCount' => new AlertCheckStateCount($alertCheckState, 1),
    ]);

    expect($alertCheckStateCountBadge->label)
        ->toStartWith('1 ');
})->with(AlertCheckState::cases());

test('render renders the alert check state count badge view', function (): void {
    $alertCheckStateCountBadge = $this->app->make(AlertCheckStateCountBadge::class, [
        'alertCheckStateCount' => new AlertCheckStateCount(fake()->randomElement(AlertCheckState::cases()), fake()->numberBetween(1, 10)),
    ]);

    expect($alertCheckStateCountBadge->render()->name())
        ->toBe('compass::components.alert-check-state-count-badge');
});
