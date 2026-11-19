<?php

declare(strict_types=1);

use Maarheeze\Compass\Alerts\AlertCheckState;
use Maarheeze\Compass\View\AlertCheckStateBadgeType;
use Maarheeze\Compass\View\BadgeType;

test('badge type is danger for a failing state', function (): void {
    $alertCheckStateBadgeType = new AlertCheckStateBadgeType();

    $badgeType = $alertCheckStateBadgeType->badgeType(AlertCheckState::FAILING);

    expect($badgeType)
        ->toBe(BadgeType::DANGER);
});

test('badge type is neutral for a muted state', function (): void {
    $alertCheckStateBadgeType = new AlertCheckStateBadgeType();

    $badgeType = $alertCheckStateBadgeType->badgeType(AlertCheckState::MUTED);

    expect($badgeType)
        ->toBe(BadgeType::NEUTRAL);
});

test('badge type is success for an ok state', function (): void {
    $alertCheckStateBadgeType = new AlertCheckStateBadgeType();

    $badgeType = $alertCheckStateBadgeType->badgeType(AlertCheckState::OK);

    expect($badgeType)
        ->toBe(BadgeType::SUCCESS);
});
