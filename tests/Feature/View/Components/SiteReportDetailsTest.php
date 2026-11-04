<?php

declare(strict_types=1);

use Maarheeze\Compass\Models\SiteReport;
use Maarheeze\Compass\View\Components\SiteReportDetails;

test('key is the key the source of the site report uses for the site', function (): void {
    $sourceKey = fake()->unique()->sha1();
    $siteReport = SiteReport::factory()->create(['key' => null]);
    $this->setConfiguration('compass.sources', [
        $siteReport->source => [
            'driver' => 'compass-client',
            'key' => $sourceKey,
            'path' => sprintf('/%s', fake()->slug()),
        ],
    ]);

    $siteReportDetails = $this->app->make(SiteReportDetails::class, ['siteReport' => $siteReport]);

    expect($siteReportDetails->key)
        ->toBe($sourceKey);
});

test('path is the path the source of the site report uses for the site', function (): void {
    $sourcePath = sprintf('/%s', fake()->slug());
    $siteReport = SiteReport::factory()->create(['path' => null]);
    $this->setConfiguration('compass.sources', [
        $siteReport->source => [
            'driver' => 'compass-client',
            'key' => fake()->sha1(),
            'path' => $sourcePath,
        ],
    ]);

    $siteReportDetails = $this->app->make(SiteReportDetails::class, ['siteReport' => $siteReport]);

    expect($siteReportDetails->path)
        ->toBe($sourcePath);
});

test('render renders the site report details view', function (): void {
    $siteReport = SiteReport::factory()->create();
    $this->setConfiguration('compass.sources', [
        $siteReport->source => [
            'driver' => 'compass-client',
            'key' => fake()->sha1(),
            'path' => sprintf('/%s', fake()->slug()),
        ],
    ]);

    $siteReportDetails = $this->app->make(SiteReportDetails::class, ['siteReport' => $siteReport]);

    expect($siteReportDetails->render()->name())
        ->toBe('compass::components.site-report-details');
});
