<?php

declare(strict_types=1);

use Maarheeze\Compass\Collector\SiteReports\CollectedSiteReport;
use Maarheeze\Compass\Collector\SiteReports\SiteReportStatus;
use Maarheeze\Compass\Collector\SiteReports\SiteReportSynchronizer;
use Maarheeze\Compass\Models\Alert;
use Maarheeze\Compass\Models\SiteEndpoint;
use Maarheeze\Compass\Models\SiteReport;

test('store report stores what an installed client reported', function (): void {
    $siteReport = SiteReport::factory()->create();
    $collectedSiteReport = CollectedSiteReport::installed(fake()->boolean(), fake()->word(), fake()->company(), []);

    $siteReportSynchronizer = $this->app->make(SiteReportSynchronizer::class);
    $siteReportSynchronizer->storeReport($siteReport, $collectedSiteReport);

    $this->assertDatabaseHas(SiteReport::class, [
        'id' => $siteReport->id,
        'status' => SiteReportStatus::INSTALLED,
        'app_debug' => $collectedSiteReport->appDebug,
        'app_env' => $collectedSiteReport->appEnv,
        'app_name' => $collectedSiteReport->appName,
    ]);
});

test('store report stores a site report not stored yet', function (CollectedSiteReport $collectedSiteReport): void {
    $siteReport = SiteReport::factory()->make();

    $siteReportSynchronizer = $this->app->make(SiteReportSynchronizer::class);
    $siteReportSynchronizer->storeReport($siteReport, $collectedSiteReport);

    $this->assertDatabaseHas(SiteReport::class, [
        'site_id' => $siteReport->site_id,
        'source' => $siteReport->source,
        'status' => $collectedSiteReport->status,
    ]);
})->with([
    'installed' => function (): CollectedSiteReport {
        return CollectedSiteReport::installed(fake()->boolean(), fake()->word(), fake()->company(), []);
    },
    'unauthorized' => function (): CollectedSiteReport {
        return CollectedSiteReport::unauthorized();
    },
]);

test('store report stores the reported endpoints', function (): void {
    $siteReport = SiteReport::factory()->create();
    $endpoint = sprintf('/%s', fake()->slug());
    $collectedSiteReport = CollectedSiteReport::installed(fake()->boolean(), fake()->word(), fake()->company(), [$endpoint]);

    $siteReportSynchronizer = $this->app->make(SiteReportSynchronizer::class);
    $siteReportSynchronizer->storeReport($siteReport, $collectedSiteReport);

    $this->assertDatabaseHas(SiteEndpoint::class, [
        'site_report_id' => $siteReport->id,
        'path' => $endpoint,
    ]);
});

test('store report keeps the endpoints that are still reported', function (): void {
    $siteEndpoint = SiteEndpoint::factory()->create();
    $collectedSiteReport = CollectedSiteReport::installed(fake()->boolean(), fake()->word(), fake()->company(), [$siteEndpoint->path->toString()]);

    $siteReportSynchronizer = $this->app->make(SiteReportSynchronizer::class);
    $siteReportSynchronizer->storeReport($siteEndpoint->siteReport, $collectedSiteReport);

    $this->assertModelExists($siteEndpoint);
});

test('store report deletes the endpoints that are no longer reported', function (): void {
    $siteEndpoint = SiteEndpoint::factory()->create();
    $collectedSiteReport = CollectedSiteReport::installed(fake()->boolean(), fake()->word(), fake()->company(), []);

    $siteReportSynchronizer = $this->app->make(SiteReportSynchronizer::class);
    $siteReportSynchronizer->storeReport($siteEndpoint->siteReport, $collectedSiteReport);

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
    $siteReportSynchronizer->storeReport($siteEndpoint->siteReport, $collectedSiteReport);

    $this->assertModelMissing($alert);
});

test('store report keeps the alerts of the endpoints that are still reported', function (): void {
    $siteEndpoint = SiteEndpoint::factory()->create();
    $alert = Alert::factory()
        ->siteEndpointDown()
        ->for($siteEndpoint, 'subject')
        ->create();
    $collectedSiteReport = CollectedSiteReport::installed(fake()->boolean(), fake()->word(), fake()->company(), [$siteEndpoint->path->toString()]);

    $siteReportSynchronizer = $this->app->make(SiteReportSynchronizer::class);
    $siteReportSynchronizer->storeReport($siteEndpoint->siteReport, $collectedSiteReport);

    $this->assertModelExists($alert);
});

test('store report clears the report of a client that is not installed', function (): void {
    $siteReport = SiteReport::factory()->create();

    $siteReportSynchronizer = $this->app->make(SiteReportSynchronizer::class);
    $siteReportSynchronizer->storeReport($siteReport, CollectedSiteReport::notInstalled());

    $this->assertDatabaseHas(SiteReport::class, [
        'id' => $siteReport->id,
        'status' => SiteReportStatus::NOT_INSTALLED,
        'app_debug' => null,
        'app_env' => null,
        'app_name' => null,
    ]);
});

test('store report deletes the endpoints of a client that is not installed', function (): void {
    $siteEndpoint = SiteEndpoint::factory()->create();

    $siteReportSynchronizer = $this->app->make(SiteReportSynchronizer::class);
    $siteReportSynchronizer->storeReport($siteEndpoint->siteReport, CollectedSiteReport::notInstalled());

    $this->assertModelMissing($siteEndpoint);
});

test('store report deletes the alerts of the endpoints of a client that is not installed', function (): void {
    $siteEndpoint = SiteEndpoint::factory()->create();
    $alert = Alert::factory()
        ->siteEndpointDown()
        ->for($siteEndpoint, 'subject')
        ->create();

    $siteReportSynchronizer = $this->app->make(SiteReportSynchronizer::class);
    $siteReportSynchronizer->storeReport($siteEndpoint->siteReport, CollectedSiteReport::notInstalled());

    $this->assertModelMissing($alert);
});

test('store report stores the status of a client that is incompatible or refuses the key', function (
    CollectedSiteReport $collectedSiteReport,
    SiteReportStatus $siteReportStatus,
): void {
    $siteReport = SiteReport::factory()->create();

    $siteReportSynchronizer = $this->app->make(SiteReportSynchronizer::class);
    $siteReportSynchronizer->storeReport($siteReport, $collectedSiteReport);

    $this->assertDatabaseHas(SiteReport::class, [
        'id' => $siteReport->id,
        'status' => $siteReportStatus,
    ]);
})->with([
    'incompatible' => [CollectedSiteReport::incompatible(), SiteReportStatus::INCOMPATIBLE],
    'unauthorized' => [CollectedSiteReport::unauthorized(), SiteReportStatus::UNAUTHORIZED],
]);

test('store report clears the report of a client that is incompatible or refuses the key', function (CollectedSiteReport $collectedSiteReport): void {
    $siteReport = SiteReport::factory()->create([
        'app_debug' => fake()->boolean(),
        'app_env' => fake()->word(),
        'app_name' => fake()->company(),
    ]);

    $siteReportSynchronizer = $this->app->make(SiteReportSynchronizer::class);
    $siteReportSynchronizer->storeReport($siteReport, $collectedSiteReport);

    $this->assertDatabaseHas(SiteReport::class, [
        'id' => $siteReport->id,
        'app_debug' => null,
        'app_env' => null,
        'app_name' => null,
    ]);
})->with([
    'incompatible' => [CollectedSiteReport::incompatible()],
    'unauthorized' => [CollectedSiteReport::unauthorized()],
]);

test('store report keeps the endpoints of a client that is incompatible or refuses the key', function (CollectedSiteReport $collectedSiteReport): void {
    $siteEndpoint = SiteEndpoint::factory()->create();

    $siteReportSynchronizer = $this->app->make(SiteReportSynchronizer::class);
    $siteReportSynchronizer->storeReport($siteEndpoint->siteReport, $collectedSiteReport);

    $this->assertModelExists($siteEndpoint);
})->with([
    'incompatible' => [CollectedSiteReport::incompatible()],
    'unauthorized' => [CollectedSiteReport::unauthorized()],
]);
