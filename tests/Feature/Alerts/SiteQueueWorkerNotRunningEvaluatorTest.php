<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Maarheeze\Compass\Alerts\ConditionState;
use Maarheeze\Compass\Alerts\SiteQueueWorkerNotRunningEvaluator;
use Maarheeze\Compass\Alerts\Types\SiteQueueWorkerNotRunning;
use Maarheeze\Compass\Collector\SiteProcesses\QueueWorkerState;
use Maarheeze\Compass\Collector\SiteReports\SiteReportStatus;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\Models\SiteReport;
use Psr\Clock\ClockInterface;
use Symfony\Component\Clock\MockClock;

test('conditionObservation observes a queue worker not running now for a site that queues jobs', function (): void {
    $now = CarbonImmutable::parse(fake()->iso8601());
    $this->app->instance(ClockInterface::class, new MockClock($now));
    $site = Site::factory()->create([
        'queue_worker_state' => fake()->randomElement([
            QueueWorkerState::FAILING,
            QueueWorkerState::NOT_CONFIGURED,
            QueueWorkerState::STOPPED,
        ]),
    ]);
    SiteReport::factory()->create([
        'site_id' => $site->id,
        'status' => SiteReportStatus::INSTALLED,
        'queue_driver' => fake()->randomElement(['database', 'redis']),
    ]);

    $siteQueueWorkerNotRunningEvaluator = $this->app->make(SiteQueueWorkerNotRunningEvaluator::class);
    $conditionObservation = $siteQueueWorkerNotRunningEvaluator->conditionObservation($site);

    expect($conditionObservation?->alertType)
        ->toBeInstanceOf(SiteQueueWorkerNotRunning::class)
        ->and($conditionObservation?->subject)
        ->toBe($site)
        ->and($conditionObservation?->condition)
        ->toBe(ConditionState::ACTIVE)
        ->and($conditionObservation?->observedAt->equalTo($now))
        ->toBeTrue();
});

test('conditionObservation observes a running queue worker no longer not running now', function (): void {
    $now = CarbonImmutable::parse(fake()->iso8601());
    $this->app->instance(ClockInterface::class, new MockClock($now));
    $site = Site::factory()->create(['queue_worker_state' => QueueWorkerState::RUNNING]);
    SiteReport::factory()->create([
        'site_id' => $site->id,
        'status' => SiteReportStatus::INSTALLED,
        'queue_driver' => fake()->randomElement(['database', 'redis']),
    ]);

    $siteQueueWorkerNotRunningEvaluator = $this->app->make(SiteQueueWorkerNotRunningEvaluator::class);
    $conditionObservation = $siteQueueWorkerNotRunningEvaluator->conditionObservation($site);

    expect($conditionObservation?->alertType)
        ->toBeInstanceOf(SiteQueueWorkerNotRunning::class)
        ->and($conditionObservation?->subject)
        ->toBe($site)
        ->and($conditionObservation?->condition)
        ->toBe(ConditionState::RESOLVED)
        ->and($conditionObservation?->observedAt->equalTo($now))
        ->toBeTrue();
});

test('conditionObservation observes a queue worker not applicable now for a site that runs its jobs synchronously', function (): void {
    $now = CarbonImmutable::parse(fake()->iso8601());
    $this->app->instance(ClockInterface::class, new MockClock($now));
    $site = Site::factory()->create();
    SiteReport::factory()->create([
        'site_id' => $site->id,
        'status' => SiteReportStatus::INSTALLED,
        'queue_driver' => 'sync',
    ]);

    $siteQueueWorkerNotRunningEvaluator = $this->app->make(SiteQueueWorkerNotRunningEvaluator::class);
    $conditionObservation = $siteQueueWorkerNotRunningEvaluator->conditionObservation($site);

    expect($conditionObservation?->alertType)
        ->toBeInstanceOf(SiteQueueWorkerNotRunning::class)
        ->and($conditionObservation?->subject)
        ->toBe($site)
        ->and($conditionObservation?->condition)
        ->toBe(ConditionState::NOT_APPLICABLE)
        ->and($conditionObservation?->observedAt->equalTo($now))
        ->toBeTrue();
});

test('conditionObservation observes nothing for a site without an installed report', function (): void {
    $site = Site::factory()->create();

    $siteQueueWorkerNotRunningEvaluator = $this->app->make(SiteQueueWorkerNotRunningEvaluator::class);
    $conditionObservation = $siteQueueWorkerNotRunningEvaluator->conditionObservation($site);

    expect($conditionObservation)
        ->toBeNull();
});
