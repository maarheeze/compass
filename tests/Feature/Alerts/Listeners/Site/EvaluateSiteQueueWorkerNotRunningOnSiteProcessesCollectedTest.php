<?php

declare(strict_types=1);

use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Support\Testing\Fakes\BusFake;
use Maarheeze\Compass\Alerts\ConditionObservation;
use Maarheeze\Compass\Alerts\Jobs\HandleConditionObservations;
use Maarheeze\Compass\Alerts\Listeners\Site\EvaluateSiteQueueWorkerNotRunningOnSiteProcessesCollected;
use Maarheeze\Compass\Alerts\Types\SiteQueueWorkerNotRunning;
use Maarheeze\Compass\Collector\Events\SiteProcessesCollected;
use Maarheeze\Compass\Collector\SiteProcesses\QueueWorkerState;
use Maarheeze\Compass\Collector\SiteReports\SiteReportStatus;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\Models\SiteReport;

test('handle observes the site whose processes were collected', function (): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(QueueingDispatcher::class, $busFake);
    $site = Site::factory()->create([
        'queue_worker_state' => fake()->randomElement([QueueWorkerState::RUNNING, QueueWorkerState::STOPPED]),
    ]);
    SiteReport::factory()->create([
        'site_id' => $site->id,
        'status' => SiteReportStatus::INSTALLED,
    ]);

    $evaluateSiteQueueWorkerNotRunningOnSiteProcessesCollected = $this->app->make(EvaluateSiteQueueWorkerNotRunningOnSiteProcessesCollected::class);
    $evaluateSiteQueueWorkerNotRunningOnSiteProcessesCollected->handle(new SiteProcessesCollected($site));

    $busFake->assertDispatched(
        HandleConditionObservations::class,
        static function (HandleConditionObservations $handleConditionObservations) use ($site): bool {
            return $handleConditionObservations->conditionObservations->contains(static function (ConditionObservation $conditionObservation) use ($site): bool {
                return $conditionObservation->alertType instanceof SiteQueueWorkerNotRunning
                    && $conditionObservation->subject === $site;
            });
        },
    );
});

test('handle observes nothing for a site without an installed report', function (): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(QueueingDispatcher::class, $busFake);
    $site = Site::factory()->create();

    $evaluateSiteQueueWorkerNotRunningOnSiteProcessesCollected = $this->app->make(EvaluateSiteQueueWorkerNotRunningOnSiteProcessesCollected::class);
    $evaluateSiteQueueWorkerNotRunningOnSiteProcessesCollected->handle(new SiteProcessesCollected($site));

    $busFake->assertNotDispatched(HandleConditionObservations::class);
});
