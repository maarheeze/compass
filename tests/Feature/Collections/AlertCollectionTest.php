<?php

declare(strict_types=1);

use Maarheeze\Compass\Alerts\AlertTypes;
use Maarheeze\Compass\Collections\AlertCollection;
use Maarheeze\Compass\Models\Alert;

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
