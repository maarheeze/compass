<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Support\Testing\Fakes\BusFake;
use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Alerts\ConditionState;
use Maarheeze\Compass\Alerts\Events\ConditionObserved;
use Maarheeze\Compass\Alerts\Jobs\HandleConditionObservations;
use Maarheeze\Compass\Alerts\Listeners\Client\EvaluateSiteClientIncompatible;
use Maarheeze\Compass\Collector\Events\SiteInspected;
use Maarheeze\Compass\Collector\SiteReports\SiteReportStatus;
use Maarheeze\Compass\Models\Site;
use Psr\Clock\ClockInterface;
use Symfony\Component\Clock\MockClock;

test('handle observes incompatible client now for a site with an outdated client', function (): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(QueueingDispatcher::class, $busFake);
    $now = CarbonImmutable::parse(fake()->iso8601());
    $this->app->instance(ClockInterface::class, new MockClock($now));
    $site = Site::factory()->create(['client_status' => SiteReportStatus::INCOMPATIBLE]);

    $evaluateIncompatibleClient = $this->app->make(EvaluateSiteClientIncompatible::class);
    $evaluateIncompatibleClient->handle(new SiteInspected($site));

    $busFake->assertDispatched(
        HandleConditionObservations::class,
        static function (HandleConditionObservations $handleConditionObservations) use ($now, $site): bool {
            return $handleConditionObservations->conditionObservations->contains(static function (ConditionObserved $conditionObserved) use ($now, $site): bool {
                return $conditionObserved->alertType === AlertType::SITE_CLIENT_INCOMPATIBLE
                    && $conditionObserved->subject === $site
                    && $conditionObserved->condition === ConditionState::ACTIVE
                    && $conditionObserved->observedAt->equalTo($now);
            });
        },
    );
});

test('handle observes incompatible client resolved now for a site with any other client status', function (?SiteReportStatus $siteReportStatus): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(QueueingDispatcher::class, $busFake);
    $now = CarbonImmutable::parse(fake()->iso8601());
    $this->app->instance(ClockInterface::class, new MockClock($now));
    $site = Site::factory()->create(['client_status' => $siteReportStatus]);

    $evaluateIncompatibleClient = $this->app->make(EvaluateSiteClientIncompatible::class);
    $evaluateIncompatibleClient->handle(new SiteInspected($site));

    $busFake->assertDispatched(
        HandleConditionObservations::class,
        static function (HandleConditionObservations $handleConditionObservations) use ($now, $site): bool {
            return $handleConditionObservations->conditionObservations->contains(static function (ConditionObserved $conditionObserved) use ($now, $site): bool {
                return $conditionObserved->alertType === AlertType::SITE_CLIENT_INCOMPATIBLE
                    && $conditionObserved->subject === $site
                    && $conditionObserved->condition === ConditionState::RESOLVED
                    && $conditionObserved->observedAt->equalTo($now);
            });
        },
    );
})->with([
    SiteReportStatus::INSTALLED,
    SiteReportStatus::NOT_INSTALLED,
    SiteReportStatus::UNAUTHORIZED,
    null,
]);
