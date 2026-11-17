<?php

declare(strict_types=1);

use Maarheeze\Compass\Alerts\AlertLevel;
use Maarheeze\Compass\Alerts\AlertLevelCount;
use Maarheeze\Compass\View\AlertLevelBadgeType;
use Maarheeze\Compass\View\Components\AlertLevelCountBadge;

test('badge type is the badge type of the level', function (): void {
    $alertLevel = fake()->randomElement(AlertLevel::cases());

    $alertLevelCountBadge = $this->app->make(AlertLevelCountBadge::class, [
        'alertLevelCount' => new AlertLevelCount($alertLevel, fake()->numberBetween(1, 10)),
    ]);

    expect($alertLevelCountBadge->badgeType)
        ->toBe(new AlertLevelBadgeType()->badgeType($alertLevel));
});

test('label is the translated count of the level', function (): void {
    $alertLevelCountBadge = $this->app->make(AlertLevelCountBadge::class, [
        'alertLevelCount' => new AlertLevelCount(AlertLevel::WARNING, 2),
    ]);

    expect($alertLevelCountBadge->label)
        ->toBe('2 Warnings');
});

test('label counts the alerts of every level', function (AlertLevel $alertLevel): void {
    $alertLevelCountBadge = $this->app->make(AlertLevelCountBadge::class, [
        'alertLevelCount' => new AlertLevelCount($alertLevel, 1),
    ]);

    expect($alertLevelCountBadge->label)
        ->toStartWith('1 ');
})->with(AlertLevel::cases());

test('render renders the alert level count badge view', function (): void {
    $alertLevelCountBadge = $this->app->make(AlertLevelCountBadge::class, [
        'alertLevelCount' => new AlertLevelCount(fake()->randomElement(AlertLevel::cases()), fake()->numberBetween(1, 10)),
    ]);

    expect($alertLevelCountBadge->render()->name())
        ->toBe('compass::components.alert-level-count-badge');
});
