<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Support\Testing\Fakes\BusFake;
use Maarheeze\Compass\Alerts\ConditionObservation;
use Maarheeze\Compass\Alerts\ConditionState;
use Maarheeze\Compass\Alerts\Jobs\HandleConditionObservations;
use Maarheeze\Compass\Alerts\Listeners\Site\ResolveMissingSiteConditions;
use Maarheeze\Compass\Alerts\Types\SiteDebugEnabled;
use Maarheeze\Compass\Alerts\Types\SiteEndpointDown;
use Maarheeze\Compass\Alerts\Types\SiteNotInProduction;
use Maarheeze\Compass\Alerts\Types\SiteReportIncompatible;
use Maarheeze\Compass\Alerts\Types\SiteReportUnauthorized;
use Maarheeze\Compass\Collector\Events\SitesCollected;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\Models\SiteEndpoint;
use Maarheeze\Compass\Models\SiteReport;
use Psr\Clock\ClockInterface;
use Symfony\Component\Clock\MockClock;

test('handle observes a client condition of a report of a missing site resolved now', function (string $alertTypeClass): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(QueueingDispatcher::class, $busFake);
    $now = CarbonImmutable::parse(fake()->iso8601());
    $this->app->instance(ClockInterface::class, new MockClock($now));
    $siteReport = SiteReport::factory()
        ->for(Site::factory()->state(['missing_at' => fake()->dateTime()]))
        ->create();

    $resolveMissingSiteConditions = $this->app->make(ResolveMissingSiteConditions::class);
    $resolveMissingSiteConditions->handle(new SitesCollected($siteReport->site->source));

    $busFake->assertDispatched(
        HandleConditionObservations::class,
        static function (HandleConditionObservations $handleConditionObservations) use ($alertTypeClass, $now, $siteReport): bool {
            return $handleConditionObservations->conditionObservations->contains(static function (ConditionObservation $conditionObservation) use ($alertTypeClass, $now, $siteReport): bool {
                return $conditionObservation->alertType instanceof $alertTypeClass
                    && $conditionObservation->subject instanceof SiteReport
                    && $conditionObservation->subject->is($siteReport)
                    && $conditionObservation->condition === ConditionState::RESOLVED
                    && $conditionObservation->observedAt->equalTo($now);
            });
        },
    );
})->with([
    SiteDebugEnabled::class,
    SiteReportIncompatible::class,
    SiteNotInProduction::class,
    SiteReportUnauthorized::class,
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
    $resolveMissingSiteConditions->handle(new SitesCollected($siteEndpoint->siteReport->site->source));

    $busFake->assertDispatched(
        HandleConditionObservations::class,
        static function (HandleConditionObservations $handleConditionObservations) use ($now, $siteEndpoint): bool {
            return $handleConditionObservations->conditionObservations->contains(static function (ConditionObservation $conditionObservation) use ($now, $siteEndpoint): bool {
                return $conditionObservation->alertType instanceof SiteEndpointDown
                    && $conditionObservation->subject instanceof SiteEndpoint
                    && $conditionObservation->subject->is($siteEndpoint)
                    && $conditionObservation->condition === ConditionState::RESOLVED
                    && $conditionObservation->observedAt->equalTo($now);
            });
        },
    );
});

test('handle observes nothing for a collected site and its endpoints', function (): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(QueueingDispatcher::class, $busFake);
    $site = Site::factory()->create(['missing_at' => null]);
    SiteEndpoint::factory()
        ->for(SiteReport::factory()->for($site))
        ->create();

    $resolveMissingSiteConditions = $this->app->make(ResolveMissingSiteConditions::class);
    $resolveMissingSiteConditions->handle(new SitesCollected($site->source));

    $busFake->assertNothingDispatched();
});

test('handle observes nothing for a missing site of another source', function (): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(QueueingDispatcher::class, $busFake);
    $siteFactory = Site::factory()->state([
        'source' => fake()->unique()->slug(),
        'missing_at' => fake()->dateTime(),
    ]);
    SiteEndpoint::factory()
        ->for(SiteReport::factory()->for($siteFactory))
        ->create();

    $resolveMissingSiteConditions = $this->app->make(ResolveMissingSiteConditions::class);
    $resolveMissingSiteConditions->handle(new SitesCollected(fake()->unique()->slug()));

    $busFake->assertNothingDispatched();
});
