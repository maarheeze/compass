<?php

declare(strict_types=1);

use Maarheeze\Compass\Collector\Client\SiteReportSynchronizer;
use Maarheeze\Compass\Collector\SiteReports\CollectedSiteReport;
use Maarheeze\Compass\Collector\SiteReports\SiteReportStatus;
use Maarheeze\Compass\Models\Alert;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\Models\SiteEndpoint;

test('store report stores what an installed client reported', function (): void {
    $site = Site::factory()->create();
    $collectedSiteReport = CollectedSiteReport::installed(fake()->boolean(), fake()->word(), fake()->company(), []);

    $siteReportSynchronizer = $this->app->make(SiteReportSynchronizer::class);
    $siteReportSynchronizer->storeReport($site, $collectedSiteReport);

    $this->assertDatabaseHas(Site::class, [
        'id' => $site->id,
        'client_status' => SiteReportStatus::INSTALLED,
        'app_debug' => $collectedSiteReport->appDebug,
        'app_env' => $collectedSiteReport->appEnv,
        'app_name' => $collectedSiteReport->appName,
    ]);
});

test('store report stores the reported endpoints', function (): void {
    $site = Site::factory()->create();
    $endpoint = sprintf('/%s', fake()->slug());
    $collectedSiteReport = CollectedSiteReport::installed(fake()->boolean(), fake()->word(), fake()->company(), [$endpoint]);

    $siteReportSynchronizer = $this->app->make(SiteReportSynchronizer::class);
    $siteReportSynchronizer->storeReport($site, $collectedSiteReport);

    $this->assertDatabaseHas(SiteEndpoint::class, [
        'site_id' => $site->id,
        'path' => $endpoint,
    ]);
});

test('store report keeps the endpoints that are still reported', function (): void {
    $siteEndpoint = SiteEndpoint::factory()->create();
    $collectedSiteReport = CollectedSiteReport::installed(fake()->boolean(), fake()->word(), fake()->company(), [$siteEndpoint->path]);

    $siteReportSynchronizer = $this->app->make(SiteReportSynchronizer::class);
    $siteReportSynchronizer->storeReport($siteEndpoint->site, $collectedSiteReport);

    $this->assertModelExists($siteEndpoint);
});

test('store report deletes the endpoints that are no longer reported', function (): void {
    $siteEndpoint = SiteEndpoint::factory()->create();
    $collectedSiteReport = CollectedSiteReport::installed(fake()->boolean(), fake()->word(), fake()->company(), []);

    $siteReportSynchronizer = $this->app->make(SiteReportSynchronizer::class);
    $siteReportSynchronizer->storeReport($siteEndpoint->site, $collectedSiteReport);

    $this->assertModelMissing($siteEndpoint);
});

test('store report deletes the alerts of the endpoints that are no longer reported', function (): void {
    $siteEndpoint = SiteEndpoint::factory()->create();
    $alert = Alert::factory()
        ->siteEndpointDown()
        ->for($siteEndpoint, 'subject')
        ->create();
    $collectedSiteReport = CollectedSiteReport::installed(fake()->boolean(), fake()->word(), fake()->company(), []);

    $siteReportSynchronizer = $this->app->make(SiteReportSynchronizer::class);
    $siteReportSynchronizer->storeReport($siteEndpoint->site, $collectedSiteReport);

    $this->assertModelMissing($alert);
});

test('store report keeps the alerts of the endpoints that are still reported', function (): void {
    $siteEndpoint = SiteEndpoint::factory()->create();
    $alert = Alert::factory()
        ->siteEndpointDown()
        ->for($siteEndpoint, 'subject')
        ->create();
    $collectedSiteReport = CollectedSiteReport::installed(fake()->boolean(), fake()->word(), fake()->company(), [$siteEndpoint->path]);

    $siteReportSynchronizer = $this->app->make(SiteReportSynchronizer::class);
    $siteReportSynchronizer->storeReport($siteEndpoint->site, $collectedSiteReport);

    $this->assertModelExists($alert);
});

test('store report clears the report of a client that is not installed', function (): void {
    $site = Site::factory()->create();

    $siteReportSynchronizer = $this->app->make(SiteReportSynchronizer::class);
    $siteReportSynchronizer->storeReport($site, CollectedSiteReport::notInstalled());

    $this->assertDatabaseHas(Site::class, [
        'id' => $site->id,
        'client_status' => SiteReportStatus::NOT_INSTALLED,
        'app_debug' => null,
        'app_env' => null,
        'app_name' => null,
    ]);
});

test('store report deletes the endpoints of a client that is not installed', function (): void {
    $siteEndpoint = SiteEndpoint::factory()->create();

    $siteReportSynchronizer = $this->app->make(SiteReportSynchronizer::class);
    $siteReportSynchronizer->storeReport($siteEndpoint->site, CollectedSiteReport::notInstalled());

    $this->assertModelMissing($siteEndpoint);
});

test('store report deletes the alerts of the endpoints of a client that is not installed', function (): void {
    $siteEndpoint = SiteEndpoint::factory()->create();
    $alert = Alert::factory()
        ->siteEndpointDown()
        ->for($siteEndpoint, 'subject')
        ->create();

    $siteReportSynchronizer = $this->app->make(SiteReportSynchronizer::class);
    $siteReportSynchronizer->storeReport($siteEndpoint->site, CollectedSiteReport::notInstalled());

    $this->assertModelMissing($alert);
});

test('store report stores the status of a client that is incompatible or refuses the key', function (
    CollectedSiteReport $collectedSiteReport,
    SiteReportStatus $siteReportStatus,
): void {
    $site = Site::factory()->create();

    $siteReportSynchronizer = $this->app->make(SiteReportSynchronizer::class);
    $siteReportSynchronizer->storeReport($site, $collectedSiteReport);

    $this->assertDatabaseHas(Site::class, [
        'id' => $site->id,
        'client_status' => $siteReportStatus,
    ]);
})->with([
    'incompatible' => [CollectedSiteReport::incompatible(), SiteReportStatus::INCOMPATIBLE],
    'unauthorized' => [CollectedSiteReport::unauthorized(), SiteReportStatus::UNAUTHORIZED],
]);

test('store report keeps the report of a client that is incompatible or refuses the key', function (CollectedSiteReport $collectedSiteReport): void {
    $site = Site::factory()->create([
        'app_debug' => fake()->boolean(),
        'app_env' => fake()->word(),
        'app_name' => fake()->company(),
    ]);

    $siteReportSynchronizer = $this->app->make(SiteReportSynchronizer::class);
    $siteReportSynchronizer->storeReport($site, $collectedSiteReport);

    $this->assertDatabaseHas(Site::class, [
        'id' => $site->id,
        'app_debug' => $site->app_debug,
        'app_env' => $site->app_env,
        'app_name' => $site->app_name,
    ]);
})->with([
    'incompatible' => [CollectedSiteReport::incompatible()],
    'unauthorized' => [CollectedSiteReport::unauthorized()],
]);

test('store report keeps the endpoints of a client that is incompatible or refuses the key', function (CollectedSiteReport $collectedSiteReport): void {
    $siteEndpoint = SiteEndpoint::factory()->create();

    $siteReportSynchronizer = $this->app->make(SiteReportSynchronizer::class);
    $siteReportSynchronizer->storeReport($siteEndpoint->site, $collectedSiteReport);

    $this->assertModelExists($siteEndpoint);
})->with([
    'incompatible' => [CollectedSiteReport::incompatible()],
    'unauthorized' => [CollectedSiteReport::unauthorized()],
]);
