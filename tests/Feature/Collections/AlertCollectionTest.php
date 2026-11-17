<?php

declare(strict_types=1);

use Maarheeze\Compass\Alerts\AlertLevel;
use Maarheeze\Compass\Alerts\AlertLevelCount;
use Maarheeze\Compass\Alerts\AlertTypes;
use Maarheeze\Compass\Collections\AlertCollection;
use Maarheeze\Compass\Models\Alert;

test('level counts count the alerts per level, highest level first', function (): void {
    $alerts = new AlertCollection([
        Alert::factory()
            ->siteBehind()
            ->make(),
        Alert::factory()
            ->siteEndpointDown()
            ->make(),
        Alert::factory()
            ->repositoryMissing()
            ->make(),
    ]);

    $levelCounts = $alerts->levelCounts($this->app->make(AlertTypes::class));

    expect($levelCounts->all())
        ->toEqual([new AlertLevelCount(AlertLevel::CRITICAL, 1), new AlertLevelCount(AlertLevel::NOTICE, 2)]);
});

test('level counts are empty without alerts', function (): void {
    $alerts = new AlertCollection();

    $levelCounts = $alerts->levelCounts($this->app->make(AlertTypes::class));

    expect($levelCounts)
        ->toBeEmpty();
});

test('sorted by level is the alerts with the highest level first', function (): void {
    $noticeAlert = Alert::factory()
        ->siteBehind()
        ->make();
    $criticalAlert = Alert::factory()
        ->siteEndpointDown()
        ->make();
    $warningAlert = Alert::factory()
        ->composerTypeMissing()
        ->make();
    $alerts = new AlertCollection([$noticeAlert, $criticalAlert, $warningAlert]);

    $alertsSortedByLevel = $alerts->sortedByLevel($this->app->make(AlertTypes::class));

    expect($alertsSortedByLevel->all())
        ->toBe([$criticalAlert, $warningAlert, $noticeAlert]);
});

test('sorted by level keeps the order of the alerts within a level', function (): void {
    $firstAlert = Alert::factory()
        ->siteEndpointDown()
        ->make();
    $secondAlert = Alert::factory()
        ->siteDebugEnabled()
        ->make();
    $alerts = new AlertCollection([$firstAlert, $secondAlert]);

    $alertsSortedByLevel = $alerts->sortedByLevel($this->app->make(AlertTypes::class));

    expect($alertsSortedByLevel->all())
        ->toBe([$firstAlert, $secondAlert]);
});
