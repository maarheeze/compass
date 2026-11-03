<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Support\Testing\Fakes\BusFake;
use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Alerts\ConditionState;
use Maarheeze\Compass\Alerts\Events\ConditionObserved;
use Maarheeze\Compass\Alerts\Jobs\HandleConditionObservations;
use Maarheeze\Compass\Alerts\Listeners\Client\EvaluateSiteNotInProduction;
use Maarheeze\Compass\Collector\Events\SiteInspected;
use Maarheeze\Compass\Models\Site;
use Psr\Clock\ClockInterface;
use Symfony\Component\Clock\MockClock;

test('handle observes non production now for a site that is not in production', function (?string $appEnv): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(QueueingDispatcher::class, $busFake);
    $now = CarbonImmutable::parse(fake()->iso8601());
    $this->app->instance(ClockInterface::class, new MockClock($now));
    $site = Site::factory()->create(['app_env' => $appEnv]);

    $evaluateNonProduction = $this->app->make(EvaluateSiteNotInProduction::class);
    $evaluateNonProduction->handle(new SiteInspected($site));

    $busFake->assertDispatched(
        HandleConditionObservations::class,
        static function (HandleConditionObservations $handleConditionObservations) use ($now, $site): bool {
            return $handleConditionObservations->conditionObservations->contains(static function (ConditionObserved $conditionObserved) use ($now, $site): bool {
                return $conditionObserved->alertType === AlertType::SITE_NOT_IN_PRODUCTION
                    && $conditionObserved->subject === $site
                    && $conditionObserved->condition === ConditionState::ACTIVE
                    && $conditionObserved->observedAt->equalTo($now);
            });
        },
    );
})->with(['local', 'staging', 'testing', null]);

test('handle observes non production resolved now for a site in production', function (): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(QueueingDispatcher::class, $busFake);
    $now = CarbonImmutable::parse(fake()->iso8601());
    $this->app->instance(ClockInterface::class, new MockClock($now));
    $site = Site::factory()->create(['app_env' => 'production']);

    $evaluateNonProduction = $this->app->make(EvaluateSiteNotInProduction::class);
    $evaluateNonProduction->handle(new SiteInspected($site));

    $busFake->assertDispatched(
        HandleConditionObservations::class,
        static function (HandleConditionObservations $handleConditionObservations) use ($now, $site): bool {
            return $handleConditionObservations->conditionObservations->contains(static function (ConditionObserved $conditionObserved) use ($now, $site): bool {
                return $conditionObserved->alertType === AlertType::SITE_NOT_IN_PRODUCTION
                    && $conditionObserved->subject === $site
                    && $conditionObserved->condition === ConditionState::RESOLVED
                    && $conditionObserved->observedAt->equalTo($now);
            });
        },
    );
});
