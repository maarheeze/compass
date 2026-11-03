<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Support\Testing\Fakes\BusFake;
use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Alerts\ConditionState;
use Maarheeze\Compass\Alerts\Events\ConditionObserved;
use Maarheeze\Compass\Alerts\Jobs\HandleConditionObservations;
use Maarheeze\Compass\Alerts\Listeners\Forge\EvaluateSiteMissing;
use Maarheeze\Compass\Collector\Events\SiteMissingIgnored;
use Maarheeze\Compass\Collector\Events\SitesCollected;
use Maarheeze\Compass\Models\Site;
use Psr\Clock\ClockInterface;
use Symfony\Component\Clock\MockClock;

test('handle observes a missing, not ignored site missing now', function (): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(QueueingDispatcher::class, $busFake);
    $now = CarbonImmutable::parse(fake()->iso8601());
    $this->app->instance(ClockInterface::class, new MockClock($now));
    $site = Site::factory()->create([
        'missing_at' => fake()->dateTime(),
        'missing_ignored_at' => null,
    ]);

    $evaluateMissingSite = $this->app->make(EvaluateSiteMissing::class);
    $evaluateMissingSite->handle(new SitesCollected());

    $busFake->assertDispatched(
        HandleConditionObservations::class,
        static function (HandleConditionObservations $handleConditionObservations) use ($now, $site): bool {
            return $handleConditionObservations->conditionObservations->contains(static function (ConditionObserved $conditionObserved) use ($now, $site): bool {
                return $conditionObserved->alertType === AlertType::SITE_MISSING
                    && $conditionObserved->subject instanceof Site
                    && $conditionObserved->subject->is($site)
                    && $conditionObserved->condition === ConditionState::ACTIVE
                    && $conditionObserved->observedAt->equalTo($now);
            });
        },
    );
});

test('handle observes a collected site no longer missing now', function (): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(QueueingDispatcher::class, $busFake);
    $now = CarbonImmutable::parse(fake()->iso8601());
    $this->app->instance(ClockInterface::class, new MockClock($now));
    $site = Site::factory()->create([
        'missing_at' => null,
        'missing_ignored_at' => null,
    ]);

    $evaluateMissingSite = $this->app->make(EvaluateSiteMissing::class);
    $evaluateMissingSite->handle(new SitesCollected());

    $busFake->assertDispatched(
        HandleConditionObservations::class,
        static function (HandleConditionObservations $handleConditionObservations) use ($now, $site): bool {
            return $handleConditionObservations->conditionObservations->contains(static function (ConditionObserved $conditionObserved) use ($now, $site): bool {
                return $conditionObserved->alertType === AlertType::SITE_MISSING
                    && $conditionObserved->subject instanceof Site
                    && $conditionObserved->subject->is($site)
                    && $conditionObserved->condition === ConditionState::RESOLVED
                    && $conditionObserved->observedAt->equalTo($now);
            });
        },
    );
});

test('handle observes a missing, ignored site no longer missing', function (): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(QueueingDispatcher::class, $busFake);
    $site = Site::factory()->create([
        'missing_at' => fake()->dateTimeBetween('-2 months', '-1 month'),
        'missing_ignored_at' => fake()->dateTimeBetween('-1 month'),
    ]);

    $evaluateMissingSite = $this->app->make(EvaluateSiteMissing::class);
    $evaluateMissingSite->handle(new SitesCollected());

    $busFake->assertDispatched(
        HandleConditionObservations::class,
        static function (HandleConditionObservations $handleConditionObservations) use ($site): bool {
            return $handleConditionObservations->conditionObservations->contains(static function (ConditionObserved $conditionObserved) use ($site): bool {
                return $conditionObserved->subject instanceof Site
                    && $conditionObserved->subject->is($site)
                    && $conditionObserved->condition === ConditionState::RESOLVED;
            });
        },
    );
});

test('handle observes an ignored site no longer missing now', function (): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(QueueingDispatcher::class, $busFake);
    $now = CarbonImmutable::parse(fake()->iso8601());
    $this->app->instance(ClockInterface::class, new MockClock($now));
    $site = Site::factory()->create([
        'missing_at' => fake()->dateTimeBetween('-2 months', '-1 month'),
        'missing_ignored_at' => fake()->dateTimeBetween('-1 month'),
    ]);

    $evaluateMissingSite = $this->app->make(EvaluateSiteMissing::class);
    $evaluateMissingSite->handle(new SiteMissingIgnored($site));

    $busFake->assertDispatched(
        HandleConditionObservations::class,
        static function (HandleConditionObservations $handleConditionObservations) use ($now, $site): bool {
            return $handleConditionObservations->conditionObservations->contains(static function (ConditionObserved $conditionObserved) use ($now, $site): bool {
                return $conditionObserved->alertType === AlertType::SITE_MISSING
                    && $conditionObserved->subject === $site
                    && $conditionObserved->condition === ConditionState::RESOLVED
                    && $conditionObserved->observedAt->equalTo($now);
            });
        },
    );
});
