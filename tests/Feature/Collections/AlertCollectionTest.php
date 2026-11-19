<?php

declare(strict_types=1);

use Maarheeze\Compass\Alerts\AlertLevel;
use Maarheeze\Compass\Alerts\AlertLevelCount;
use Maarheeze\Compass\Alerts\AlertTypes;
use Maarheeze\Compass\Collections\AlertCollection;
use Maarheeze\Compass\Models\Alert;

test('done is the alerts that are done', function (): void {
    $unmutedOpenAlert = Alert::factory()
        ->active()
        ->make();
    $mutedOpenAlert = Alert::factory()
        ->active()
        ->muted()
        ->make();
    $doneAlert = Alert::factory()
        ->done()
        ->make();
    $alerts = new AlertCollection([$unmutedOpenAlert, $mutedOpenAlert, $doneAlert]);

    $doneAlerts = $alerts->done();

    expect($doneAlerts->sole())
        ->toBe($doneAlert);
});

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

test('unmuted open is the open alerts that are not muted', function (): void {
    $unmutedOpenAlert = Alert::factory()
        ->active()
        ->make();
    $mutedOpenAlert = Alert::factory()
        ->active()
        ->muted()
        ->make();
    $doneAlert = Alert::factory()
        ->done()
        ->make();
    $alerts = new AlertCollection([$unmutedOpenAlert, $mutedOpenAlert, $doneAlert]);

    $unmutedOpenAlerts = $alerts->unmutedOpen();

    expect($unmutedOpenAlerts->sole())
        ->toBe($unmutedOpenAlert);
});

test('muted open is the open alerts that are muted', function (): void {
    $unmutedOpenAlert = Alert::factory()
        ->active()
        ->make();
    $mutedOpenAlert = Alert::factory()
        ->active()
        ->muted()
        ->make();
    $mutedDoneAlert = Alert::factory()
        ->done()
        ->muted()
        ->make();
    $alerts = new AlertCollection([$unmutedOpenAlert, $mutedOpenAlert, $mutedDoneAlert]);

    $mutedOpenAlerts = $alerts->mutedOpen();

    expect($mutedOpenAlerts->sole())
        ->toBe($mutedOpenAlert);
});

test('muted or done is the alerts that are muted or done', function (): void {
    $unmutedOpenAlert = Alert::factory()
        ->active()
        ->make();
    $mutedOpenAlert = Alert::factory()
        ->active()
        ->muted()
        ->make();
    $doneAlert = Alert::factory()
        ->done()
        ->make();
    $alerts = new AlertCollection([$unmutedOpenAlert, $mutedOpenAlert, $doneAlert]);

    $mutedOrDoneAlerts = $alerts->mutedOrDone();

    expect($mutedOrDoneAlerts->values()->all())
        ->toBe([$mutedOpenAlert, $doneAlert]);
});
