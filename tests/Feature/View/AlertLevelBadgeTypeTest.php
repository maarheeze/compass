<?php

declare(strict_types=1);

use Maarheeze\Compass\Alerts\AlertLevel;
use Maarheeze\Compass\View\AlertLevelBadgeType;
use Maarheeze\Compass\View\BadgeType;

test('badge type is danger for a critical level', function (): void {
    $alertLevelBadgeType = new AlertLevelBadgeType();

    $badgeType = $alertLevelBadgeType->badgeType(AlertLevel::CRITICAL);

    expect($badgeType)
        ->toBe(BadgeType::DANGER);
});

test('badge type is info for a notice level', function (): void {
    $alertLevelBadgeType = new AlertLevelBadgeType();

    $badgeType = $alertLevelBadgeType->badgeType(AlertLevel::NOTICE);

    expect($badgeType)
        ->toBe(BadgeType::INFO);
});

test('badge type is warning for a warning level', function (): void {
    $alertLevelBadgeType = new AlertLevelBadgeType();

    $badgeType = $alertLevelBadgeType->badgeType(AlertLevel::WARNING);

    expect($badgeType)
        ->toBe(BadgeType::WARNING);
});
