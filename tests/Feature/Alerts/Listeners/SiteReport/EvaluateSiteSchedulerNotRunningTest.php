<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Support\Testing\Fakes\BusFake;
use Maarheeze\Compass\Alerts\ConditionObservation;
use Maarheeze\Compass\Alerts\ConditionState;
use Maarheeze\Compass\Alerts\Jobs\HandleConditionObservations;
use Maarheeze\Compass\Alerts\Listeners\SiteReport\EvaluateSiteSchedulerNotRunning;
use Maarheeze\Compass\Alerts\Types\SiteSchedulerNotRunning;
use Maarheeze\Compass\Collector\Events\SiteInspected;
use Maarheeze\Compass\Collector\SiteReports\SiteReportStatus;
use Maarheeze\Compass\Models\SiteReport;
use Maarheeze\Compass\Models\SiteScheduledTask;
use Psr\Clock\ClockInterface;
use Symfony\Component\Clock\MockClock;

test('handle observes scheduler not running now for a site whose scheduler does not run', function (): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(QueueingDispatcher::class, $busFake);
    $now = CarbonImmutable::parse(fake()->iso8601());
    $this->app->instance(ClockInterface::class, new MockClock($now));
    $siteReport = SiteReport::factory()->create([
        'status' => SiteReportStatus::INSTALLED,
        'scheduler_last_ran_at' => null,
        'scheduler_grace_minutes' => fake()->numberBetween(1, 60),
    ]);
    SiteScheduledTask::factory()->create(['site_report_id' => $siteReport->id]);

    $evaluateSchedulerNotRunning = $this->app->make(EvaluateSiteSchedulerNotRunning::class);
    $evaluateSchedulerNotRunning->handle(new SiteInspected($siteReport));

    $busFake->assertDispatched(
        HandleConditionObservations::class,
        static function (HandleConditionObservations $handleConditionObservations) use ($now, $siteReport): bool {
            return $handleConditionObservations->conditionObservations->contains(static function (ConditionObservation $conditionObservation) use ($now, $siteReport): bool {
                return $conditionObservation->alertType instanceof SiteSchedulerNotRunning
                    && $conditionObservation->subject === $siteReport
                    && $conditionObservation->condition === ConditionState::ACTIVE
                    && $conditionObservation->observedAt->equalTo($now);
            });
        },
    );
});

test('handle observes scheduler not running resolved now for a site whose scheduler runs', function (): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(QueueingDispatcher::class, $busFake);
    $now = CarbonImmutable::parse(fake()->iso8601());
    $this->app->instance(ClockInterface::class, new MockClock($now));
    $collectedAt = CarbonImmutable::instance(fake()->dateTime())->startOfSecond();
    $schedulerGraceMinutes = fake()->numberBetween(1, 60);
    $siteReport = SiteReport::factory()->create([
        'status' => SiteReportStatus::INSTALLED,
        'collected_at' => $collectedAt,
        'scheduler_last_ran_at' => $collectedAt->subSeconds(fake()->numberBetween(0, $schedulerGraceMinutes * 60)),
        'scheduler_grace_minutes' => $schedulerGraceMinutes,
    ]);
    SiteScheduledTask::factory()->create(['site_report_id' => $siteReport->id]);

    $evaluateSchedulerNotRunning = $this->app->make(EvaluateSiteSchedulerNotRunning::class);
    $evaluateSchedulerNotRunning->handle(new SiteInspected($siteReport));

    $busFake->assertDispatched(
        HandleConditionObservations::class,
        static function (HandleConditionObservations $handleConditionObservations) use ($now, $siteReport): bool {
            return $handleConditionObservations->conditionObservations->contains(static function (ConditionObservation $conditionObservation) use ($now, $siteReport): bool {
                return $conditionObservation->alertType instanceof SiteSchedulerNotRunning
                    && $conditionObservation->subject === $siteReport
                    && $conditionObservation->condition === ConditionState::RESOLVED
                    && $conditionObservation->observedAt->equalTo($now);
            });
        },
    );
});

test('handle observes scheduler not running resolved now for a site without scheduled tasks', function (): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(QueueingDispatcher::class, $busFake);
    $now = CarbonImmutable::parse(fake()->iso8601());
    $this->app->instance(ClockInterface::class, new MockClock($now));
    $siteReport = SiteReport::factory()->create(['status' => SiteReportStatus::INSTALLED]);

    $evaluateSchedulerNotRunning = $this->app->make(EvaluateSiteSchedulerNotRunning::class);
    $evaluateSchedulerNotRunning->handle(new SiteInspected($siteReport));

    $busFake->assertDispatched(
        HandleConditionObservations::class,
        static function (HandleConditionObservations $handleConditionObservations) use ($now, $siteReport): bool {
            return $handleConditionObservations->conditionObservations->contains(static function (ConditionObservation $conditionObservation) use ($now, $siteReport): bool {
                return $conditionObservation->alertType instanceof SiteSchedulerNotRunning
                    && $conditionObservation->subject === $siteReport
                    && $conditionObservation->condition === ConditionState::RESOLVED
                    && $conditionObservation->observedAt->equalTo($now);
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
    SiteScheduledTask::factory()->create(['site_report_id' => $siteReport->id]);

    $evaluateSchedulerNotRunning = $this->app->make(EvaluateSiteSchedulerNotRunning::class);
    $evaluateSchedulerNotRunning->handle(new SiteInspected($siteReport));

    $busFake->assertNotDispatched(HandleConditionObservations::class);
});
