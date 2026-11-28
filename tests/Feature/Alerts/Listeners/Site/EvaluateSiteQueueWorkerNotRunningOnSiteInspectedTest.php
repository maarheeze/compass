<?php

declare(strict_types=1);

use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Support\Testing\Fakes\BusFake;
use Maarheeze\Compass\Alerts\ConditionObservation;
use Maarheeze\Compass\Alerts\Jobs\HandleConditionObservations;
use Maarheeze\Compass\Alerts\Listeners\Site\EvaluateSiteQueueWorkerNotRunningOnSiteInspected;
use Maarheeze\Compass\Alerts\Types\SiteQueueWorkerNotRunning;
use Maarheeze\Compass\Collector\Events\SiteInspected;
use Maarheeze\Compass\Collector\SiteProcesses\QueueWorkerState;
use Maarheeze\Compass\Collector\SiteReports\SiteReportStatus;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\Models\SiteReport;

test('handle observes the site of the inspected report', function (): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(QueueingDispatcher::class, $busFake);
    $site = Site::factory()->create([
        'queue_worker_state' => fake()->randomElement([QueueWorkerState::RUNNING, QueueWorkerState::STOPPED]),
    ]);
    $siteReport = SiteReport::factory()->create([
        'site_id' => $site->id,
        'status' => SiteReportStatus::INSTALLED,
        'queue_driver' => fake()->randomElement(['database', 'redis']),
    ]);

    $evaluateSiteQueueWorkerNotRunningOnSiteInspected = $this->app->make(EvaluateSiteQueueWorkerNotRunningOnSiteInspected::class);
    $evaluateSiteQueueWorkerNotRunningOnSiteInspected->handle(new SiteInspected($siteReport));

    $busFake->assertDispatched(
        HandleConditionObservations::class,
        static function (HandleConditionObservations $handleConditionObservations) use ($site): bool {
            return $handleConditionObservations->conditionObservations->contains(static function (ConditionObservation $conditionObservation) use ($site): bool {
                return $conditionObservation->alertType instanceof SiteQueueWorkerNotRunning
                    && $conditionObservation->subject instanceof Site
                    && $conditionObservation->subject->is($site);
            });
        },
    );
});

test('handle observes nothing for a site whose client did not report', function (): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(QueueingDispatcher::class, $busFake);
    $siteReport = SiteReport::factory()->create([
        'status' => fake()->randomElement([
            SiteReportStatus::INCOMPATIBLE,
            SiteReportStatus::NOT_INSTALLED,
            SiteReportStatus::UNAUTHORISED,
        ]),
    ]);

    $evaluateSiteQueueWorkerNotRunningOnSiteInspected = $this->app->make(EvaluateSiteQueueWorkerNotRunningOnSiteInspected::class);
    $evaluateSiteQueueWorkerNotRunningOnSiteInspected->handle(new SiteInspected($siteReport));

    $busFake->assertNotDispatched(HandleConditionObservations::class);
});
