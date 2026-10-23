<?php

declare(strict_types=1);

use Maarheeze\Compass\Collector\Client\ClientStatus;
use Maarheeze\Compass\Collector\Client\SiteReport;
use Maarheeze\Compass\Collector\Client\SiteReportSynchronizer;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\Models\SiteEndpoint;

test('store report stores what the client reported', function (): void {
    $site = Site::factory()->create();
    $siteReport = new SiteReport(fake()->boolean(), fake()->word(), fake()->company(), []);

    $siteReportSynchronizer = $this->app->make(SiteReportSynchronizer::class);
    $siteReportSynchronizer->storeReport($site, $siteReport);

    $this->assertDatabaseHas(Site::class, [
        'id' => $site->id,
        'client_status' => ClientStatus::INSTALLED,
        'app_debug' => $siteReport->appDebug,
        'app_env' => $siteReport->appEnv,
        'app_name' => $siteReport->appName,
    ]);
});

test('store report stores the reported endpoints', function (): void {
    $site = Site::factory()->create();
    $endpoint = sprintf('/%s', fake()->slug());
    $siteReport = new SiteReport(fake()->boolean(), fake()->word(), fake()->company(), [$endpoint]);

    $siteReportSynchronizer = $this->app->make(SiteReportSynchronizer::class);
    $siteReportSynchronizer->storeReport($site, $siteReport);

    $this->assertDatabaseHas(SiteEndpoint::class, [
        'site_id' => $site->id,
        'path' => $endpoint,
    ]);
});

test('store report deletes the endpoints that are no longer reported', function (): void {
    $siteEndpoint = SiteEndpoint::factory()->create();
    $siteReport = new SiteReport(fake()->boolean(), fake()->word(), fake()->company(), []);

    $siteReportSynchronizer = $this->app->make(SiteReportSynchronizer::class);
    $siteReportSynchronizer->storeReport($siteEndpoint->site, $siteReport);

    $this->assertModelMissing($siteEndpoint);
});

test('clear report marks the client not installed', function (): void {
    $site = Site::factory()->create();

    $siteReportSynchronizer = $this->app->make(SiteReportSynchronizer::class);
    $siteReportSynchronizer->clearReport($site);

    $this->assertDatabaseHas(Site::class, [
        'id' => $site->id,
        'client_status' => ClientStatus::NOT_INSTALLED,
        'app_debug' => null,
        'app_env' => null,
        'app_name' => null,
    ]);
});

test('clear report deletes the endpoints', function (): void {
    $siteEndpoint = SiteEndpoint::factory()->create();

    $siteReportSynchronizer = $this->app->make(SiteReportSynchronizer::class);
    $siteReportSynchronizer->clearReport($siteEndpoint->site);

    $this->assertModelMissing($siteEndpoint);
});

test('mark unauthorized marks the client unauthorized', function (): void {
    $site = Site::factory()->create();

    $siteReportSynchronizer = $this->app->make(SiteReportSynchronizer::class);
    $siteReportSynchronizer->markUnauthorized($site);

    $this->assertDatabaseHas(Site::class, [
        'id' => $site->id,
        'client_status' => ClientStatus::UNAUTHORIZED,
    ]);
});

test('mark unauthorized keeps the endpoints', function (): void {
    $siteEndpoint = SiteEndpoint::factory()->create();

    $siteReportSynchronizer = $this->app->make(SiteReportSynchronizer::class);
    $siteReportSynchronizer->markUnauthorized($siteEndpoint->site);

    $this->assertModelExists($siteEndpoint);
});

test('mark unreachable marks the client unreachable', function (): void {
    $site = Site::factory()->create();

    $siteReportSynchronizer = $this->app->make(SiteReportSynchronizer::class);
    $siteReportSynchronizer->markUnreachable($site);

    $this->assertDatabaseHas(Site::class, [
        'id' => $site->id,
        'client_status' => ClientStatus::UNREACHABLE,
    ]);
});

test('mark unreachable keeps the endpoints', function (): void {
    $siteEndpoint = SiteEndpoint::factory()->create();

    $siteReportSynchronizer = $this->app->make(SiteReportSynchronizer::class);
    $siteReportSynchronizer->markUnreachable($siteEndpoint->site);

    $this->assertModelExists($siteEndpoint);
});
