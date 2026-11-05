<?php

declare(strict_types=1);

use Maarheeze\Compass\Models\Alert;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\Models\SiteReport;

test('alerts including children keeps an alert of a report of the site', function (): void {
    $siteReport = SiteReport::factory()->create();
    $siteReportAlert = Alert::factory()
        ->siteDebugEnabled()
        ->for($siteReport, 'subject')
        ->create();

    $alertsIncludingChildren = $siteReport->site
        ->alertsIncludingChildren()
        ->get();

    expect($alertsIncludingChildren->sole()->is($siteReportAlert))
        ->toBeTrue();
});

test('display name is the name of the site', function (): void {
    $site = Site::factory()->create();

    $displayName = $site->displayName();

    expect($displayName)
        ->toBe($site->name);
});
