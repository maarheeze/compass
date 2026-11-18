<?php

declare(strict_types=1);

use Maarheeze\Compass\Collector\SiteReports\SiteReportStatus;
use Maarheeze\Compass\Models\SiteReport;
use Maarheeze\Compass\View\Components\SiteClientInstallNotice;

test('render renders the site client install notice view', function (): void {
    $siteReport = SiteReport::factory()->create();

    $siteClientInstallNotice = $this->app->make(SiteClientInstallNotice::class, ['siteReport' => $siteReport]);

    expect($siteClientInstallNotice->render()->name())
        ->toBe('compass::components.site-client-install-notice');
});

test('should render when the client is not installed', function (): void {
    $siteReport = SiteReport::factory()->create(['status' => SiteReportStatus::NOT_INSTALLED]);

    $siteClientInstallNotice = $this->app->make(SiteClientInstallNotice::class, ['siteReport' => $siteReport]);

    expect($siteClientInstallNotice->shouldRender())
        ->toBeTrue();
});

test('should not render when the client answered', function (): void {
    $siteReport = SiteReport::factory()->create([
        'status' => fake()->randomElement([
            SiteReportStatus::INCOMPATIBLE,
            SiteReportStatus::INSTALLED,
            SiteReportStatus::UNAUTHORISED,
        ]),
    ]);

    $siteClientInstallNotice = $this->app->make(SiteClientInstallNotice::class, ['siteReport' => $siteReport]);

    expect($siteClientInstallNotice->shouldRender())
        ->toBeFalse();
});
