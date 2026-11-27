<?php

declare(strict_types=1);

use Maarheeze\Compass\Analyser\Processes\QueueWorkerHealth;
use Maarheeze\Compass\Collector\SiteProcesses\QueueWorkerState;
use Maarheeze\Compass\Collector\SiteReports\SiteReportStatus;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\Models\SiteReport;
use Maarheeze\Compass\View\BadgeType;
use Maarheeze\Compass\View\Components\SiteQueueWorkerFact;

test('queue worker health is the health of the queue worker of the site', function (): void {
    $site = Site::factory()->create(['queue_worker_state' => QueueWorkerState::RUNNING]);
    SiteReport::factory()->create([
        'site_id' => $site->id,
        'status' => SiteReportStatus::INSTALLED,
        'queue_driver' => fake()->randomElement(['database', 'redis', 'sqs']),
    ]);

    $siteQueueWorkerFact = $this->app->make(SiteQueueWorkerFact::class, ['site' => $site]);

    expect($siteQueueWorkerFact->queueWorkerHealth)
        ->toBe(QueueWorkerHealth::RUNNING);
});

test('badge type is danger while a needed worker is not running', function (): void {
    $site = Site::factory()->create(['queue_worker_state' => QueueWorkerState::FAILING]);
    SiteReport::factory()->create([
        'site_id' => $site->id,
        'status' => SiteReportStatus::INSTALLED,
        'queue_driver' => fake()->randomElement(['database', 'redis', 'sqs']),
    ]);

    $siteQueueWorkerFact = $this->app->make(SiteQueueWorkerFact::class, ['site' => $site]);

    expect($siteQueueWorkerFact->badgeType)
        ->toBe(BadgeType::DANGER);
});

test('not running queue worker state is the collected state of a worker that is not running', function (): void {
    $queueWorkerState = fake()->randomElement([
        QueueWorkerState::FAILING,
        QueueWorkerState::NOT_CONFIGURED,
        QueueWorkerState::STOPPED,
    ]);
    $site = Site::factory()->create(['queue_worker_state' => $queueWorkerState]);
    SiteReport::factory()->create([
        'site_id' => $site->id,
        'status' => SiteReportStatus::INSTALLED,
        'queue_driver' => fake()->randomElement(['database', 'redis', 'sqs']),
    ]);

    $siteQueueWorkerFact = $this->app->make(SiteQueueWorkerFact::class, ['site' => $site]);

    expect($siteQueueWorkerFact->notRunningQueueWorkerState)
        ->toBe($queueWorkerState);
});

test('not running queue worker state is null for a running worker', function (): void {
    $site = Site::factory()->create(['queue_worker_state' => QueueWorkerState::RUNNING]);
    SiteReport::factory()->create([
        'site_id' => $site->id,
        'status' => SiteReportStatus::INSTALLED,
        'queue_driver' => fake()->randomElement(['database', 'redis', 'sqs']),
    ]);

    $siteQueueWorkerFact = $this->app->make(SiteQueueWorkerFact::class, ['site' => $site]);

    expect($siteQueueWorkerFact->notRunningQueueWorkerState)
        ->toBeNull();
});

test('collected at is when the processes of a site that needs a worker were collected', function (): void {
    $site = Site::factory()->create([
        'queue_worker_state' => fake()->randomElement([
            QueueWorkerState::FAILING,
            QueueWorkerState::NOT_CONFIGURED,
            QueueWorkerState::RUNNING,
            QueueWorkerState::STARTING,
            QueueWorkerState::STOPPED,
        ]),
        'processes_collected_at' => fake()->dateTime(),
    ]);
    SiteReport::factory()->create([
        'site_id' => $site->id,
        'status' => SiteReportStatus::INSTALLED,
        'queue_driver' => fake()->randomElement(['database', 'redis', 'sqs']),
    ]);

    $siteQueueWorkerFact = $this->app->make(SiteQueueWorkerFact::class, ['site' => $site]);

    expect($siteQueueWorkerFact->collectedAt)
        ->toEqual($site->processes_collected_at);
});

test('collected at is null for a site that needs no worker', function (): void {
    $site = Site::factory()->create(['processes_collected_at' => fake()->dateTime()]);
    SiteReport::factory()->create([
        'site_id' => $site->id,
        'status' => SiteReportStatus::INSTALLED,
        'queue_driver' => 'sync',
    ]);

    $siteQueueWorkerFact = $this->app->make(SiteQueueWorkerFact::class, ['site' => $site]);

    expect($siteQueueWorkerFact->collectedAt)
        ->toBeNull();
});

test('render renders the site queue worker fact view', function (): void {
    $siteQueueWorkerFact = $this->app->make(SiteQueueWorkerFact::class, ['site' => Site::factory()->create()]);

    expect($siteQueueWorkerFact->render()->name())
        ->toBe('compass::components.site-queue-worker-fact');
});
