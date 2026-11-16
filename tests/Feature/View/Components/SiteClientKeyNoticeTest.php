<?php

declare(strict_types=1);

use Maarheeze\Compass\Collector\SiteReports\SiteReportStatus;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\Models\SiteReport;
use Maarheeze\Compass\View\Components\SiteClientKeyNotice;

test('render renders the site client key notice view', function (): void {
    $siteReport = SiteReport::factory()->create();

    $siteClientKeyNotice = $this->app->make(SiteClientKeyNotice::class, ['siteReport' => $siteReport]);

    expect($siteClientKeyNotice->render()->name())
        ->toBe('compass::components.site-client-key-notice');
});

test('key is the key of the site', function (): void {
    $site = Site::factory()->create(['key' => fake()->sha1()]);
    $siteReport = SiteReport::factory()->for($site)->create();

    $siteClientKeyNotice = $this->app->make(SiteClientKeyNotice::class, ['siteReport' => $siteReport]);

    expect($siteClientKeyNotice->key)
        ->toBe($site->key);
});

test('should render when the client refused the key', function (): void {
    $siteReport = SiteReport::factory()->create(['status' => SiteReportStatus::UNAUTHORIZED]);

    $siteClientKeyNotice = $this->app->make(SiteClientKeyNotice::class, ['siteReport' => $siteReport]);

    expect($siteClientKeyNotice->shouldRender())
        ->toBeTrue();
});

test('should not render when the client did not refuse the key', function (): void {
    $siteReport = SiteReport::factory()->create([
        'status' => fake()->randomElement([
            SiteReportStatus::INCOMPATIBLE,
            SiteReportStatus::INSTALLED,
            SiteReportStatus::NOT_INSTALLED,
        ]),
    ]);

    $siteClientKeyNotice = $this->app->make(SiteClientKeyNotice::class, ['siteReport' => $siteReport]);

    expect($siteClientKeyNotice->shouldRender())
        ->toBeFalse();
});
