<?php

declare(strict_types=1);

use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\SiteReport;
use Maarheeze\Compass\View\Components\SiteReportEditLink;

test('url is the edit page of the site report', function (): void {
    $siteReport = SiteReport::factory()->create();

    $siteReportEditLink = $this->app->make(SiteReportEditLink::class, ['siteReport' => $siteReport]);

    expect($siteReportEditLink->url)
        ->toBe(route(RouteName::FORGE_SITE_REPORT_EDIT, $siteReport));
});
