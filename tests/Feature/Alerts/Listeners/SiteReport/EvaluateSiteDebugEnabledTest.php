<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Support\Testing\Fakes\BusFake;
use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Alerts\ConditionObservation;
use Maarheeze\Compass\Alerts\ConditionState;
use Maarheeze\Compass\Alerts\Jobs\HandleConditionObservations;
use Maarheeze\Compass\Alerts\Listeners\SiteReport\EvaluateSiteDebugEnabled;
use Maarheeze\Compass\Collector\Events\SiteInspected;
use Maarheeze\Compass\Models\SiteReport;
use Psr\Clock\ClockInterface;
use Symfony\Component\Clock\MockClock;

test('handle observes debug enabled now for a site in debug mode', function (): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(QueueingDispatcher::class, $busFake);
    $now = CarbonImmutable::parse(fake()->iso8601());
    $this->app->instance(ClockInterface::class, new MockClock($now));
    $siteReport = SiteReport::factory()->create(['app_debug' => true]);

    $evaluateDebugEnabled = $this->app->make(EvaluateSiteDebugEnabled::class);
    $evaluateDebugEnabled->handle(new SiteInspected($siteReport));

    $busFake->assertDispatched(
        HandleConditionObservations::class,
        static function (HandleConditionObservations $handleConditionObservations) use ($now, $siteReport): bool {
            return $handleConditionObservations->conditionObservations->contains(static function (ConditionObservation $conditionObservation) use ($now, $siteReport): bool {
                return $conditionObservation->alertType === AlertType::SITE_DEBUG_ENABLED
                    && $conditionObservation->subject === $siteReport
                    && $conditionObservation->condition === ConditionState::ACTIVE
                    && $conditionObservation->observedAt->equalTo($now);
            });
        },
    );
});

test('handle observes debug enabled resolved now for a site out of debug mode', function (): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(QueueingDispatcher::class, $busFake);
    $now = CarbonImmutable::parse(fake()->iso8601());
    $this->app->instance(ClockInterface::class, new MockClock($now));
    $siteReport = SiteReport::factory()->create(['app_debug' => false]);

    $evaluateDebugEnabled = $this->app->make(EvaluateSiteDebugEnabled::class);
    $evaluateDebugEnabled->handle(new SiteInspected($siteReport));

    $busFake->assertDispatched(
        HandleConditionObservations::class,
        static function (HandleConditionObservations $handleConditionObservations) use ($now, $siteReport): bool {
            return $handleConditionObservations->conditionObservations->contains(static function (ConditionObservation $conditionObservation) use ($now, $siteReport): bool {
                return $conditionObservation->alertType === AlertType::SITE_DEBUG_ENABLED
                    && $conditionObservation->subject === $siteReport
                    && $conditionObservation->condition === ConditionState::RESOLVED
                    && $conditionObservation->observedAt->equalTo($now);
            });
        },
    );
});

test('handle observes nothing for a site not checked for debug mode', function (): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(QueueingDispatcher::class, $busFake);
    $siteReport = SiteReport::factory()->create(['app_debug' => null]);

    $evaluateDebugEnabled = $this->app->make(EvaluateSiteDebugEnabled::class);
    $evaluateDebugEnabled->handle(new SiteInspected($siteReport));

    $busFake->assertNotDispatched(HandleConditionObservations::class);
});
