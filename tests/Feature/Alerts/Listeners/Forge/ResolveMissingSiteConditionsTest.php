<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Testing\Fakes\EventFake;
use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Alerts\ConditionState;
use Maarheeze\Compass\Alerts\Events\ConditionObserved;
use Maarheeze\Compass\Alerts\Listeners\Forge\ResolveMissingSiteConditions;
use Maarheeze\Compass\Collector\Events\ForgeRunSucceeded;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\Models\SiteEndpoint;
use Maarheeze\Uuid\Uuid;
use Psr\Clock\ClockInterface;
use Symfony\Component\Clock\MockClock;

test('handle observes a client condition of a missing site resolved now', function (AlertType $alertType): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));
    $this->app->instance(Dispatcher::class, $eventFake);
    $now = CarbonImmutable::parse(fake()->iso8601());
    $this->app->instance(ClockInterface::class, new MockClock($now));
    $site = Site::factory()->create(['missing_at' => fake()->dateTime()]);

    $resolveMissingSiteConditions = $this->app->make(ResolveMissingSiteConditions::class);
    $resolveMissingSiteConditions->handle(new ForgeRunSucceeded(Uuid::generate()));

    $eventFake->assertDispatched(ConditionObserved::class, static function (ConditionObserved $conditionObserved) use ($alertType, $now, $site): bool {
        return $conditionObserved->alertType === $alertType
            && $conditionObserved->subject instanceof Site
            && $conditionObserved->subject->is($site)
            && $conditionObserved->condition === ConditionState::RESOLVED
            && $conditionObserved->observedAt->equalTo($now);
    });
})->with([
    AlertType::SITE_DEBUG_ENABLED,
    AlertType::SITE_CLIENT_INCOMPATIBLE,
    AlertType::SITE_NOT_IN_PRODUCTION,
    AlertType::SITE_CLIENT_UNAUTHORIZED,
]);

test('handle observes the endpoint of a missing site no longer down now', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));
    $this->app->instance(Dispatcher::class, $eventFake);
    $now = CarbonImmutable::parse(fake()->iso8601());
    $this->app->instance(ClockInterface::class, new MockClock($now));
    $siteEndpoint = SiteEndpoint::factory()
        ->for(Site::factory()->state(['missing_at' => fake()->dateTime()]))
        ->create();

    $resolveMissingSiteConditions = $this->app->make(ResolveMissingSiteConditions::class);
    $resolveMissingSiteConditions->handle(new ForgeRunSucceeded(Uuid::generate()));

    $eventFake->assertDispatched(ConditionObserved::class, static function (ConditionObserved $conditionObserved) use ($now, $siteEndpoint): bool {
        return $conditionObserved->alertType === AlertType::SITE_ENDPOINT_DOWN
            && $conditionObserved->subject instanceof SiteEndpoint
            && $conditionObserved->subject->is($siteEndpoint)
            && $conditionObserved->condition === ConditionState::RESOLVED
            && $conditionObserved->observedAt->equalTo($now);
    });
});

test('handle observes nothing for a collected site and its endpoints', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));
    $this->app->instance(Dispatcher::class, $eventFake);
    SiteEndpoint::factory()
        ->for(Site::factory()->state(['missing_at' => null]))
        ->create();

    $resolveMissingSiteConditions = $this->app->make(ResolveMissingSiteConditions::class);
    $resolveMissingSiteConditions->handle(new ForgeRunSucceeded(Uuid::generate()));

    $eventFake->assertNothingDispatched();
});
