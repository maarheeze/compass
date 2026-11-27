<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Maarheeze\Compass\Collector\SiteEndpoint\EndpointPath;
use Maarheeze\Compass\Collector\SiteReports\CollectedScheduledTask;
use Maarheeze\Compass\Collector\SiteReports\CollectedSiteEndpoint;
use Maarheeze\Compass\Collector\SiteReports\CollectedSiteReport;
use Maarheeze\Compass\Collector\SiteReports\SiteReportStatus;
use Maarheeze\Compass\Collector\SiteReports\SiteReportSynchroniser;
use Maarheeze\Compass\Models\Alert;
use Maarheeze\Compass\Models\SiteEndpoint;
use Maarheeze\Compass\Models\SiteReport;
use Maarheeze\Compass\Models\SiteScheduledTask;
use Psr\Clock\ClockInterface;
use Symfony\Component\Clock\MockClock;

test('store report stores what an installed client reported', function (): void {
    $siteReport = SiteReport::factory()->create();
    $collectedSiteReport = CollectedSiteReport::installed(fake()->boolean(), fake()->word(), fake()->company(), [], fake()->boolean(), fake()->slug(), [], null);

    $siteReportSynchroniser = $this->app->make(SiteReportSynchroniser::class);
    $siteReportSynchroniser->storeReport($siteReport, $collectedSiteReport, fake()->numberBetween(1, 60));

    $this->assertDatabaseHas(SiteReport::class, [
        'id' => $siteReport->id,
        'status' => SiteReportStatus::INSTALLED,
        'app_debug' => $collectedSiteReport->appDebug,
        'app_env' => $collectedSiteReport->appEnv,
        'app_name' => $collectedSiteReport->appName,
        'maintenance_mode' => $collectedSiteReport->maintenanceMode,
    ]);
});

test('store report stores the scheduler and the queue driver an installed client reported', function (): void {
    $siteReport = SiteReport::factory()->create();
    $schedulerLastRanAt = CarbonImmutable::instance(fake()->dateTime())->startOfSecond();
    $collectedSiteReport = CollectedSiteReport::installed(
        fake()->boolean(),
        fake()->word(),
        fake()->company(),
        [],
        fake()->boolean(),
        fake()->slug(),
        [],
        $schedulerLastRanAt,
    );

    $siteReportSynchroniser = $this->app->make(SiteReportSynchroniser::class);
    $siteReportSynchroniser->storeReport($siteReport, $collectedSiteReport, fake()->numberBetween(1, 60));

    $this->assertDatabaseHas(SiteReport::class, [
        'id' => $siteReport->id,
        'queue_driver' => $collectedSiteReport->queueDriver,
        'scheduler_last_ran_at' => $schedulerLastRanAt,
    ]);
});

test('store report stores the scheduler grace minutes on the report', function (CollectedSiteReport $collectedSiteReport): void {
    $siteReport = SiteReport::factory()->create();
    $schedulerGraceMinutes = fake()->numberBetween(1, 60);

    $siteReportSynchroniser = $this->app->make(SiteReportSynchroniser::class);
    $siteReportSynchroniser->storeReport($siteReport, $collectedSiteReport, $schedulerGraceMinutes);

    $this->assertDatabaseHas(SiteReport::class, [
        'id' => $siteReport->id,
        'scheduler_grace_minutes' => $schedulerGraceMinutes,
    ]);
})->with([
    'installed' => function (): CollectedSiteReport {
        return CollectedSiteReport::installed(fake()->boolean(), fake()->word(), fake()->company(), [], fake()->boolean(), fake()->slug(), [], null);
    },
    'incompatible' => function (): CollectedSiteReport {
        return CollectedSiteReport::incompatible();
    },
]);

test('store report stores the scheduled tasks an installed client reported', function (): void {
    $siteReport = SiteReport::factory()->create();
    $collectedScheduledTask = new CollectedScheduledTask(
        sprintf('php artisan %s', fake()->slug()),
        sprintf('%d * * * *', fake()->numberBetween(0, 59)),
    );
    $collectedSiteReport = CollectedSiteReport::installed(
        fake()->boolean(),
        fake()->word(),
        fake()->company(),
        [],
        fake()->boolean(),
        fake()->slug(),
        [$collectedScheduledTask],
        null,
    );

    $siteReportSynchroniser = $this->app->make(SiteReportSynchroniser::class);
    $siteReportSynchroniser->storeReport($siteReport, $collectedSiteReport, fake()->numberBetween(1, 60));

    $this->assertDatabaseHas(SiteScheduledTask::class, [
        'site_report_id' => $siteReport->id,
        'command' => $collectedScheduledTask->command,
        'expression' => $collectedScheduledTask->expression,
    ]);
});

test('store report deletes a scheduled task the client no longer reports', function (): void {
    $siteScheduledTask = SiteScheduledTask::factory()->create();
    $collectedSiteReport = CollectedSiteReport::installed(fake()->boolean(), fake()->word(), fake()->company(), [], fake()->boolean(), fake()->slug(), [], null);

    $siteReportSynchroniser = $this->app->make(SiteReportSynchroniser::class);
    $siteReportSynchroniser->storeReport($siteScheduledTask->siteReport, $collectedSiteReport, fake()->numberBetween(1, 60));

    $this->assertModelMissing($siteScheduledTask);
});

test('store report stamps the report with the moment it was collected', function (): void {
    $now = CarbonImmutable::parse(fake()->iso8601());
    $this->app->instance(ClockInterface::class, new MockClock($now));
    $siteReport = SiteReport::factory()->create();
    $collectedSiteReport = CollectedSiteReport::installed(fake()->boolean(), fake()->word(), fake()->company(), [], fake()->boolean(), fake()->slug(), [], null);

    $siteReportSynchroniser = $this->app->make(SiteReportSynchroniser::class);
    $siteReportSynchroniser->storeReport($siteReport, $collectedSiteReport, fake()->numberBetween(1, 60));

    $this->assertDatabaseHas(SiteReport::class, [
        'id' => $siteReport->id,
        'collected_at' => $now,
    ]);
});

test('store report stores a site report not stored yet', function (CollectedSiteReport $collectedSiteReport): void {
    $siteReport = SiteReport::factory()->make();

    $siteReportSynchroniser = $this->app->make(SiteReportSynchroniser::class);
    $siteReportSynchroniser->storeReport($siteReport, $collectedSiteReport, fake()->numberBetween(1, 60));

    $this->assertDatabaseHas(SiteReport::class, [
        'site_id' => $siteReport->site_id,
        'source' => $siteReport->source,
        'status' => $collectedSiteReport->status,
    ]);
})->with([
    'installed' => function (): CollectedSiteReport {
        return CollectedSiteReport::installed(fake()->boolean(), fake()->word(), fake()->company(), [], fake()->boolean(), fake()->slug(), [], null);
    },
    'unauthorised' => function (): CollectedSiteReport {
        return CollectedSiteReport::unauthorised();
    },
]);

test('store report stores the reported endpoints', function (): void {
    $siteReport = SiteReport::factory()->create();
    $collectedSiteEndpoint = new CollectedSiteEndpoint(
        new EndpointPath(sprintf('/%s', fake()->slug())),
        fake()->numberBetween(1, 60),
    );
    $collectedSiteReport = CollectedSiteReport::installed(fake()->boolean(), fake()->word(), fake()->company(), [$collectedSiteEndpoint], fake()->boolean(), fake()->slug(), [], null);

    $siteReportSynchroniser = $this->app->make(SiteReportSynchroniser::class);
    $siteReportSynchroniser->storeReport($siteReport, $collectedSiteReport, fake()->numberBetween(1, 60));

    $this->assertDatabaseHas(SiteEndpoint::class, [
        'site_report_id' => $siteReport->id,
        'path' => $collectedSiteEndpoint->path,
        'frequency_minutes' => $collectedSiteEndpoint->frequencyMinutes,
    ]);
});

test('store report stores the reported frequency of an endpoint that is still reported', function (): void {
    $siteEndpoint = SiteEndpoint::factory()->create();
    $frequencyMinutes = fake()->numberBetween(1, 60);
    $collectedSiteReport = CollectedSiteReport::installed(fake()->boolean(), fake()->word(), fake()->company(), [
        new CollectedSiteEndpoint($siteEndpoint->path, $frequencyMinutes),
    ], fake()->boolean(), fake()->slug(), [], null);

    $siteReportSynchroniser = $this->app->make(SiteReportSynchroniser::class);
    $siteReportSynchroniser->storeReport($siteEndpoint->siteReport, $collectedSiteReport, fake()->numberBetween(1, 60));

    $this->assertDatabaseHas(SiteEndpoint::class, [
        'id' => $siteEndpoint->id,
        'frequency_minutes' => $frequencyMinutes,
    ]);
});

test('store report makes a new endpoint due at once', function (): void {
    $now = CarbonImmutable::parse(fake()->iso8601());
    $this->app->instance(ClockInterface::class, new MockClock($now));
    $siteReport = SiteReport::factory()->create();
    $collectedSiteEndpoint = new CollectedSiteEndpoint(
        new EndpointPath(sprintf('/%s', fake()->slug())),
        fake()->numberBetween(1, 60),
    );
    $collectedSiteReport = CollectedSiteReport::installed(fake()->boolean(), fake()->word(), fake()->company(), [$collectedSiteEndpoint], fake()->boolean(), fake()->slug(), [], null);

    $siteReportSynchroniser = $this->app->make(SiteReportSynchroniser::class);
    $siteReportSynchroniser->storeReport($siteReport, $collectedSiteReport, fake()->numberBetween(1, 60));

    $this->assertDatabaseHas(SiteEndpoint::class, [
        'site_report_id' => $siteReport->id,
        'path' => $collectedSiteEndpoint->path,
        'due_at' => $now,
    ]);
});

test('store report makes an endpoint whose frequency changed due at once', function (): void {
    $now = CarbonImmutable::parse(fake()->iso8601());
    $this->app->instance(ClockInterface::class, new MockClock($now));
    $siteEndpoint = SiteEndpoint::factory()->create([
        'frequency_minutes' => fake()->numberBetween(1, 30),
        'due_at' => $now->addMinutes(fake()->numberBetween(1, 60)),
    ]);
    $collectedSiteReport = CollectedSiteReport::installed(fake()->boolean(), fake()->word(), fake()->company(), [
        new CollectedSiteEndpoint($siteEndpoint->path, fake()->numberBetween(31, 60)),
    ], fake()->boolean(), fake()->slug(), [], null);

    $siteReportSynchroniser = $this->app->make(SiteReportSynchroniser::class);
    $siteReportSynchroniser->storeReport($siteEndpoint->siteReport, $collectedSiteReport, fake()->numberBetween(1, 60));

    $this->assertDatabaseHas(SiteEndpoint::class, [
        'id' => $siteEndpoint->id,
        'due_at' => $now,
    ]);
});

test('store report keeps the due time of an endpoint whose frequency did not change', function (): void {
    $siteEndpoint = SiteEndpoint::factory()->create();
    $collectedSiteReport = CollectedSiteReport::installed(fake()->boolean(), fake()->word(), fake()->company(), [
        new CollectedSiteEndpoint($siteEndpoint->path, $siteEndpoint->frequency_minutes),
    ], fake()->boolean(), fake()->slug(), [], null);

    $siteReportSynchroniser = $this->app->make(SiteReportSynchroniser::class);
    $siteReportSynchroniser->storeReport($siteEndpoint->siteReport, $collectedSiteReport, fake()->numberBetween(1, 60));

    $this->assertDatabaseHas(SiteEndpoint::class, [
        'id' => $siteEndpoint->id,
        'due_at' => $siteEndpoint->due_at,
    ]);
});

test('store report keeps the endpoints that are still reported', function (): void {
    $siteEndpoint = SiteEndpoint::factory()->create();
    $collectedSiteReport = CollectedSiteReport::installed(fake()->boolean(), fake()->word(), fake()->company(), [
        new CollectedSiteEndpoint($siteEndpoint->path, fake()->numberBetween(1, 60)),
    ], fake()->boolean(), fake()->slug(), [], null);

    $siteReportSynchroniser = $this->app->make(SiteReportSynchroniser::class);
    $siteReportSynchroniser->storeReport($siteEndpoint->siteReport, $collectedSiteReport, fake()->numberBetween(1, 60));

    $this->assertModelExists($siteEndpoint);
});

test('store report deletes the endpoints that are no longer reported', function (): void {
    $siteEndpoint = SiteEndpoint::factory()->create();
    $collectedSiteReport = CollectedSiteReport::installed(fake()->boolean(), fake()->word(), fake()->company(), [], fake()->boolean(), fake()->slug(), [], null);

    $siteReportSynchroniser = $this->app->make(SiteReportSynchroniser::class);
    $siteReportSynchroniser->storeReport($siteEndpoint->siteReport, $collectedSiteReport, fake()->numberBetween(1, 60));

    $this->assertModelMissing($siteEndpoint);
});

test('store report deletes the alerts of the endpoints that are no longer reported', function (): void {
    $siteEndpoint = SiteEndpoint::factory()->create();
    $alert = Alert::factory()
        ->siteEndpointDown()
        ->for($siteEndpoint, 'subject')
        ->create();
    $collectedSiteReport = CollectedSiteReport::installed(fake()->boolean(), fake()->word(), fake()->company(), [], fake()->boolean(), fake()->slug(), [], null);

    $siteReportSynchroniser = $this->app->make(SiteReportSynchroniser::class);
    $siteReportSynchroniser->storeReport($siteEndpoint->siteReport, $collectedSiteReport, fake()->numberBetween(1, 60));

    $this->assertModelMissing($alert);
});

test('store report keeps the alerts of the endpoints that are still reported', function (): void {
    $siteEndpoint = SiteEndpoint::factory()->create();
    $alert = Alert::factory()
        ->siteEndpointDown()
        ->for($siteEndpoint, 'subject')
        ->create();
    $collectedSiteReport = CollectedSiteReport::installed(fake()->boolean(), fake()->word(), fake()->company(), [
        new CollectedSiteEndpoint($siteEndpoint->path, fake()->numberBetween(1, 60)),
    ], fake()->boolean(), fake()->slug(), [], null);

    $siteReportSynchroniser = $this->app->make(SiteReportSynchroniser::class);
    $siteReportSynchroniser->storeReport($siteEndpoint->siteReport, $collectedSiteReport, fake()->numberBetween(1, 60));

    $this->assertModelExists($alert);
});

test('store report clears the report of a client that is not installed', function (): void {
    $siteReport = SiteReport::factory()->create();

    $siteReportSynchroniser = $this->app->make(SiteReportSynchroniser::class);
    $siteReportSynchroniser->storeReport($siteReport, CollectedSiteReport::notInstalled(), fake()->numberBetween(1, 60));

    $this->assertDatabaseHas(SiteReport::class, [
        'id' => $siteReport->id,
        'status' => SiteReportStatus::NOT_INSTALLED,
        'app_debug' => null,
        'app_env' => null,
        'app_name' => null,
        'maintenance_mode' => null,
    ]);
});

test('store report deletes the endpoints of a client that is not installed', function (): void {
    $siteEndpoint = SiteEndpoint::factory()->create();

    $siteReportSynchroniser = $this->app->make(SiteReportSynchroniser::class);
    $siteReportSynchroniser->storeReport($siteEndpoint->siteReport, CollectedSiteReport::notInstalled(), fake()->numberBetween(1, 60));

    $this->assertModelMissing($siteEndpoint);
});

test('store report deletes the alerts of the endpoints of a client that is not installed', function (): void {
    $siteEndpoint = SiteEndpoint::factory()->create();
    $alert = Alert::factory()
        ->siteEndpointDown()
        ->for($siteEndpoint, 'subject')
        ->create();

    $siteReportSynchroniser = $this->app->make(SiteReportSynchroniser::class);
    $siteReportSynchroniser->storeReport($siteEndpoint->siteReport, CollectedSiteReport::notInstalled(), fake()->numberBetween(1, 60));

    $this->assertModelMissing($alert);
});

test('store report stores the status of a client that is incompatible or refuses the key', function (
    CollectedSiteReport $collectedSiteReport,
    SiteReportStatus $siteReportStatus,
): void {
    $siteReport = SiteReport::factory()->create();

    $siteReportSynchroniser = $this->app->make(SiteReportSynchroniser::class);
    $siteReportSynchroniser->storeReport($siteReport, $collectedSiteReport, fake()->numberBetween(1, 60));

    $this->assertDatabaseHas(SiteReport::class, [
        'id' => $siteReport->id,
        'status' => $siteReportStatus,
    ]);
})->with([
    'incompatible' => [CollectedSiteReport::incompatible(), SiteReportStatus::INCOMPATIBLE],
    'unauthorised' => [CollectedSiteReport::unauthorised(), SiteReportStatus::UNAUTHORISED],
]);

test('store report clears the report of a client that is incompatible or refuses the key', function (CollectedSiteReport $collectedSiteReport): void {
    $siteReport = SiteReport::factory()->create([
        'app_debug' => fake()->boolean(),
        'app_env' => fake()->word(),
        'app_name' => fake()->company(),
        'maintenance_mode' => fake()->boolean(),
    ]);

    $siteReportSynchroniser = $this->app->make(SiteReportSynchroniser::class);
    $siteReportSynchroniser->storeReport($siteReport, $collectedSiteReport, fake()->numberBetween(1, 60));

    $this->assertDatabaseHas(SiteReport::class, [
        'id' => $siteReport->id,
        'app_debug' => null,
        'app_env' => null,
        'app_name' => null,
        'maintenance_mode' => null,
    ]);
})->with([
    'incompatible' => [CollectedSiteReport::incompatible()],
    'unauthorised' => [CollectedSiteReport::unauthorised()],
]);

test('store report keeps the endpoints of a client that is incompatible or refuses the key', function (CollectedSiteReport $collectedSiteReport): void {
    $siteEndpoint = SiteEndpoint::factory()->create();

    $siteReportSynchroniser = $this->app->make(SiteReportSynchroniser::class);
    $siteReportSynchroniser->storeReport($siteEndpoint->siteReport, $collectedSiteReport, fake()->numberBetween(1, 60));

    $this->assertModelExists($siteEndpoint);
})->with([
    'incompatible' => [CollectedSiteReport::incompatible()],
    'unauthorised' => [CollectedSiteReport::unauthorised()],
]);
