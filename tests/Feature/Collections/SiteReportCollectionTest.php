<?php

declare(strict_types=1);

use Maarheeze\Compass\Collections\SiteReportCollection;
use Maarheeze\Compass\Models\SiteReport;

test('has maintenance mode on when one of the site reports has maintenance mode on', function (): void {
    $siteReports = new SiteReportCollection([
        SiteReport::factory()->make(['maintenance_mode' => false]),
        SiteReport::factory()->make(['maintenance_mode' => true]),
    ]);

    $hasMaintenanceModeOn = $siteReports->hasMaintenanceModeOn();

    expect($hasMaintenanceModeOn)
        ->toBeTrue();
});

test('has no maintenance mode on when no site report has maintenance mode on', function (): void {
    $siteReports = new SiteReportCollection([
        SiteReport::factory()->make(['maintenance_mode' => false]),
        SiteReport::factory()->make(['maintenance_mode' => null]),
    ]);

    $hasMaintenanceModeOn = $siteReports->hasMaintenanceModeOn();

    expect($hasMaintenanceModeOn)
        ->toBeFalse();
});
