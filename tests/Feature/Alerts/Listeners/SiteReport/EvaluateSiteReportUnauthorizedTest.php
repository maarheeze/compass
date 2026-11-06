<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Support\Testing\Fakes\BusFake;
use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Alerts\ConditionObservation;
use Maarheeze\Compass\Alerts\ConditionState;
use Maarheeze\Compass\Alerts\Jobs\HandleConditionObservations;
use Maarheeze\Compass\Alerts\Listeners\SiteReport\EvaluateSiteReportUnauthorized;
use Maarheeze\Compass\Collector\Events\SiteInspected;
use Maarheeze\Compass\Collector\SiteReports\SiteReportStatus;
use Maarheeze\Compass\Models\SiteReport;
use Psr\Clock\ClockInterface;
use Symfony\Component\Clock\MockClock;

test('handle observes unauthorized client now for a site that refused the key', function (): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(QueueingDispatcher::class, $busFake);
    $now = CarbonImmutable::parse(fake()->iso8601());
    $this->app->instance(ClockInterface::class, new MockClock($now));
    $siteReport = SiteReport::factory()->create(['status' => SiteReportStatus::UNAUTHORIZED]);

    $evaluateSiteReportUnauthorized = $this->app->make(EvaluateSiteReportUnauthorized::class);
    $evaluateSiteReportUnauthorized->handle(new SiteInspected($siteReport));

    $busFake->assertDispatched(
        HandleConditionObservations::class,
        static function (HandleConditionObservations $handleConditionObservations) use ($now, $siteReport): bool {
            return $handleConditionObservations->conditionObservations->contains(static function (ConditionObservation $conditionObservation) use ($now, $siteReport): bool {
                return $conditionObservation->alertType === AlertType::SITE_REPORT_UNAUTHORIZED
                    && $conditionObservation->subject === $siteReport
                    && $conditionObservation->condition === ConditionState::ACTIVE
                    && $conditionObservation->observedAt->equalTo($now);
            });
        },
    );
});

test('handle observes unauthorized client resolved now for a site with any other client status', function (SiteReportStatus $siteReportStatus): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(QueueingDispatcher::class, $busFake);
    $now = CarbonImmutable::parse(fake()->iso8601());
    $this->app->instance(ClockInterface::class, new MockClock($now));
    $siteReport = SiteReport::factory()->create(['status' => $siteReportStatus]);

    $evaluateSiteReportUnauthorized = $this->app->make(EvaluateSiteReportUnauthorized::class);
    $evaluateSiteReportUnauthorized->handle(new SiteInspected($siteReport));

    $busFake->assertDispatched(
        HandleConditionObservations::class,
        static function (HandleConditionObservations $handleConditionObservations) use ($now, $siteReport): bool {
            return $handleConditionObservations->conditionObservations->contains(static function (ConditionObservation $conditionObservation) use ($now, $siteReport): bool {
                return $conditionObservation->alertType === AlertType::SITE_REPORT_UNAUTHORIZED
                    && $conditionObservation->subject === $siteReport
                    && $conditionObservation->condition === ConditionState::RESOLVED
                    && $conditionObservation->observedAt->equalTo($now);
            });
        },
    );
})->with([
    SiteReportStatus::INCOMPATIBLE,
    SiteReportStatus::INSTALLED,
    SiteReportStatus::NOT_INSTALLED,
]);
