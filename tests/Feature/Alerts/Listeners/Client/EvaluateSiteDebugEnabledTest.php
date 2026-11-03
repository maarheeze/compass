<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Support\Testing\Fakes\BusFake;
use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Alerts\ConditionState;
use Maarheeze\Compass\Alerts\Events\ConditionObserved;
use Maarheeze\Compass\Alerts\Jobs\HandleConditionObservations;
use Maarheeze\Compass\Alerts\Listeners\Client\EvaluateSiteDebugEnabled;
use Maarheeze\Compass\Collector\Events\SiteInspected;
use Maarheeze\Compass\Models\Site;
use Psr\Clock\ClockInterface;
use Symfony\Component\Clock\MockClock;

test('handle observes debug enabled now for a site in debug mode', function (): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(QueueingDispatcher::class, $busFake);
    $now = CarbonImmutable::parse(fake()->iso8601());
    $this->app->instance(ClockInterface::class, new MockClock($now));
    $site = Site::factory()->create(['app_debug' => true]);

    $evaluateDebugEnabled = $this->app->make(EvaluateSiteDebugEnabled::class);
    $evaluateDebugEnabled->handle(new SiteInspected($site));

    $busFake->assertDispatched(
        HandleConditionObservations::class,
        static function (HandleConditionObservations $handleConditionObservations) use ($now, $site): bool {
            return $handleConditionObservations->conditionObservations->contains(static function (ConditionObserved $conditionObserved) use ($now, $site): bool {
                return $conditionObserved->alertType === AlertType::SITE_DEBUG_ENABLED
                    && $conditionObserved->subject === $site
                    && $conditionObserved->condition === ConditionState::ACTIVE
                    && $conditionObserved->observedAt->equalTo($now);
            });
        },
    );
});

test('handle observes debug enabled resolved now for a site out of debug mode', function (?bool $appDebug): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(QueueingDispatcher::class, $busFake);
    $now = CarbonImmutable::parse(fake()->iso8601());
    $this->app->instance(ClockInterface::class, new MockClock($now));
    $site = Site::factory()->create(['app_debug' => $appDebug]);

    $evaluateDebugEnabled = $this->app->make(EvaluateSiteDebugEnabled::class);
    $evaluateDebugEnabled->handle(new SiteInspected($site));

    $busFake->assertDispatched(
        HandleConditionObservations::class,
        static function (HandleConditionObservations $handleConditionObservations) use ($now, $site): bool {
            return $handleConditionObservations->conditionObservations->contains(static function (ConditionObserved $conditionObserved) use ($now, $site): bool {
                return $conditionObserved->alertType === AlertType::SITE_DEBUG_ENABLED
                    && $conditionObserved->subject === $site
                    && $conditionObserved->condition === ConditionState::RESOLVED
                    && $conditionObserved->observedAt->equalTo($now);
            });
        },
    );
})->with([false, null]);
