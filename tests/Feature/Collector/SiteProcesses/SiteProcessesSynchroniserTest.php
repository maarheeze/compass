<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Maarheeze\Compass\Collector\SiteProcesses\CollectedSiteProcesses;
use Maarheeze\Compass\Collector\SiteProcesses\QueueWorkerState;
use Maarheeze\Compass\Collector\SiteProcesses\SiteProcessesSynchroniser;
use Maarheeze\Compass\Models\Site;
use Psr\Clock\ClockInterface;
use Symfony\Component\Clock\MockClock;

test('store site processes stores the state of the queue worker of the site', function (): void {
    $site = Site::factory()->create(['queue_worker_state' => null]);
    $queueWorkerState = fake()->randomElement(QueueWorkerState::cases());

    $siteProcessesSynchroniser = $this->app->make(SiteProcessesSynchroniser::class);
    $siteProcessesSynchroniser->storeSiteProcesses($site, new CollectedSiteProcesses($queueWorkerState));

    $this->assertDatabaseHas(Site::class, [
        'id' => $site->id,
        'queue_worker_state' => $queueWorkerState,
    ]);
});

test('store site processes stamps the site with the moment its processes were collected', function (): void {
    $now = CarbonImmutable::parse(fake()->iso8601());
    $this->app->instance(ClockInterface::class, new MockClock($now));
    $site = Site::factory()->create(['processes_collected_at' => null]);

    $siteProcessesSynchroniser = $this->app->make(SiteProcessesSynchroniser::class);
    $siteProcessesSynchroniser->storeSiteProcesses(
        $site,
        new CollectedSiteProcesses(fake()->randomElement(QueueWorkerState::cases())),
    );

    $this->assertDatabaseHas(Site::class, [
        'id' => $site->id,
        'processes_collected_at' => $now,
    ]);
});
