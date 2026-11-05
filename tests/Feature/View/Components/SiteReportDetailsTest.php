<?php

declare(strict_types=1);

use Maarheeze\Compass\Models\SiteReport;
use Maarheeze\Compass\View\Components\SiteReportDetails;

test('render renders the site report details view', function (): void {
    $siteReport = SiteReport::factory()->create();

    $siteReportDetails = $this->app->make(SiteReportDetails::class, ['siteReport' => $siteReport]);

    expect($siteReportDetails->render()->name())
        ->toBe('compass::components.site-report-details');
});
