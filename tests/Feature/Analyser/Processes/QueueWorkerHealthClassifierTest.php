<?php

declare(strict_types=1);

use Maarheeze\Compass\Analyser\Processes\QueueWorkerHealth;
use Maarheeze\Compass\Analyser\Processes\QueueWorkerHealthClassifier;
use Maarheeze\Compass\Collector\SiteProcesses\QueueWorkerState;
use Maarheeze\Compass\Collector\SiteReports\SiteReportStatus;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\Models\SiteReport;

test('classify is unknown when no client reported the queue of the site', function (): void {
    $site = Site::factory()->create(['queue_worker_state' => fake()->randomElement(QueueWorkerState::cases())]);
    SiteReport::factory()->create([
        'site_id' => $site->id,
        'status' => fake()->randomElement([
            SiteReportStatus::INCOMPATIBLE,
            SiteReportStatus::NOT_INSTALLED,
            SiteReportStatus::UNAUTHORISED,
        ]),
    ]);

    $queueWorkerHealthClassifier = $this->app->make(QueueWorkerHealthClassifier::class);
    $queueWorkerHealth = $queueWorkerHealthClassifier->classify($site);

    expect($queueWorkerHealth)
        ->toBe(QueueWorkerHealth::UNKNOWN);
});

test('classify is unknown when no client reported the queue driver of the site', function (): void {
    $site = Site::factory()->create(['queue_worker_state' => fake()->randomElement(QueueWorkerState::cases())]);
    SiteReport::factory()->create([
        'site_id' => $site->id,
        'status' => SiteReportStatus::INSTALLED,
        'queue_driver' => null,
    ]);

    $queueWorkerHealthClassifier = $this->app->make(QueueWorkerHealthClassifier::class);
    $queueWorkerHealth = $queueWorkerHealthClassifier->classify($site);

    expect($queueWorkerHealth)
        ->toBe(QueueWorkerHealth::UNKNOWN);
});

test('classify is not needed when the site runs its jobs on the sync driver', function (): void {
    $site = Site::factory()->create(['queue_worker_state' => fake()->randomElement(QueueWorkerState::cases())]);
    SiteReport::factory()->create([
        'site_id' => $site->id,
        'status' => SiteReportStatus::INSTALLED,
        'queue_driver' => 'sync',
    ]);

    $queueWorkerHealthClassifier = $this->app->make(QueueWorkerHealthClassifier::class);
    $queueWorkerHealth = $queueWorkerHealthClassifier->classify($site);

    expect($queueWorkerHealth)
        ->toBe(QueueWorkerHealth::NOT_NEEDED);
});

test('classify follows the state of the queue worker of a site that queues jobs', function (
    ?QueueWorkerState $queueWorkerState,
    QueueWorkerHealth $queueWorkerHealth,
): void {
    $site = Site::factory()->create(['queue_worker_state' => $queueWorkerState]);
    SiteReport::factory()->create([
        'site_id' => $site->id,
        'status' => SiteReportStatus::INSTALLED,
        'queue_driver' => fake()->randomElement(['database', 'redis', 'sqs']),
    ]);

    $queueWorkerHealthClassifier = $this->app->make(QueueWorkerHealthClassifier::class);
    $classifiedQueueWorkerHealth = $queueWorkerHealthClassifier->classify($site);

    expect($classifiedQueueWorkerHealth)
        ->toBe($queueWorkerHealth);
})->with([
    'not collected yet' => [null, QueueWorkerHealth::UNKNOWN],
    'unknown' => [QueueWorkerState::UNKNOWN, QueueWorkerHealth::UNKNOWN],
    'running' => [QueueWorkerState::RUNNING, QueueWorkerHealth::RUNNING],
    'starting' => [QueueWorkerState::STARTING, QueueWorkerHealth::RUNNING],
    'not configured' => [QueueWorkerState::NOT_CONFIGURED, QueueWorkerHealth::NOT_RUNNING],
    'stopped' => [QueueWorkerState::STOPPED, QueueWorkerHealth::NOT_RUNNING],
    'failing' => [QueueWorkerState::FAILING, QueueWorkerHealth::NOT_RUNNING],
]);
