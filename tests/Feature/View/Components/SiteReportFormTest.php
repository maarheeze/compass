<?php

declare(strict_types=1);

use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\SiteReport;
use Maarheeze\Compass\View\Components\SiteReportForm;

test('update url is the update action of the site report', function (): void {
    $siteReport = SiteReport::factory()->create();

    $siteReportForm = $this->app->make(SiteReportForm::class, [
        'siteReport' => $siteReport,
        'key' => fake()->optional()->sha1(),
        'path' => fake()->optional()->slug(),
    ]);

    expect($siteReportForm->updateUrl)
        ->toBe(route(RouteName::FORGE_SITE_REPORT_UPDATE, $siteReport));
});

test('cancel url is the page of the site', function (): void {
    $siteReport = SiteReport::factory()->create();

    $siteReportForm = $this->app->make(SiteReportForm::class, [
        'siteReport' => $siteReport,
        'key' => fake()->optional()->sha1(),
        'path' => fake()->optional()->slug(),
    ]);

    expect($siteReportForm->cancelUrl)
        ->toBe(route(RouteName::FORGE_SITE, $siteReport->site));
});
