<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Support\Testing\Fakes\BusFake;
use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Alerts\ConditionState;
use Maarheeze\Compass\Alerts\Events\ConditionObserved;
use Maarheeze\Compass\Alerts\Jobs\HandleConditionObservations;
use Maarheeze\Compass\Alerts\Listeners\Forge\ResolveMissingSiteConditions;
use Maarheeze\Compass\Collector\Events\SitesCollected;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\Models\SiteEndpoint;
use Maarheeze\Compass\Models\SiteReport;
use Psr\Clock\ClockInterface;
use Symfony\Component\Clock\MockClock;

test('handle observes a client condition of a missing site resolved now', function (AlertType $alertType): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(QueueingDispatcher::class, $busFake);
    $now = CarbonImmutable::parse(fake()->iso8601());
    $this->app->instance(ClockInterface::class, new MockClock($now));
    $site = Site::factory()->create(['missing_at' => fake()->dateTime()]);

    $resolveMissingSiteConditions = $this->app->make(ResolveMissingSiteConditions::class);
    $resolveMissingSiteConditions->handle(new SitesCollected());

    $busFake->assertDispatched(
        HandleConditionObservations::class,
        static function (HandleConditionObservations $handleConditionObservations) use ($alertType, $now, $site): bool {
            return $handleConditionObservations->conditionObservations->contains(static function (ConditionObserved $conditionObserved) use ($alertType, $now, $site): bool {
                return $conditionObserved->alertType === $alertType
                    && $conditionObserved->subject instanceof Site
                    && $conditionObserved->subject->is($site)
                    && $conditionObserved->condition === ConditionState::RESOLVED
                    && $conditionObserved->observedAt->equalTo($now);
            });
        },
    );
})->with([
    AlertType::SITE_DEBUG_ENABLED,
    AlertType::SITE_CLIENT_INCOMPATIBLE,
    AlertType::SITE_NOT_IN_PRODUCTION,
    AlertType::SITE_CLIENT_UNAUTHORIZED,
]);

test('handle observes the endpoint of a missing site no longer down now', function (): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(QueueingDispatcher::class, $busFake);
    $now = CarbonImmutable::parse(fake()->iso8601());
    $this->app->instance(ClockInterface::class, new MockClock($now));
    $siteEndpoint = SiteEndpoint::factory()
        ->for(SiteReport::factory()->for(Site::factory()->state(['missing_at' => fake()->dateTime()])))
        ->create();

    $resolveMissingSiteConditions = $this->app->make(ResolveMissingSiteConditions::class);
    $resolveMissingSiteConditions->handle(new SitesCollected());

    $busFake->assertDispatched(
        HandleConditionObservations::class,
        static function (HandleConditionObservations $handleConditionObservations) use ($now, $siteEndpoint): bool {
            return $handleConditionObservations->conditionObservations->contains(static function (ConditionObserved $conditionObserved) use ($now, $siteEndpoint): bool {
                return $conditionObserved->alertType === AlertType::SITE_ENDPOINT_DOWN
                    && $conditionObserved->subject instanceof SiteEndpoint
                    && $conditionObserved->subject->is($siteEndpoint)
                    && $conditionObserved->condition === ConditionState::RESOLVED
                    && $conditionObserved->observedAt->equalTo($now);
            });
        },
    );
});

test('handle observes nothing for a collected site and its endpoints', function (): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(QueueingDispatcher::class, $busFake);
    SiteEndpoint::factory()
        ->for(SiteReport::factory()->for(Site::factory()->state(['missing_at' => null])))
        ->create();

    $resolveMissingSiteConditions = $this->app->make(ResolveMissingSiteConditions::class);
    $resolveMissingSiteConditions->handle(new SitesCollected());

    $busFake->assertNothingDispatched();
});
