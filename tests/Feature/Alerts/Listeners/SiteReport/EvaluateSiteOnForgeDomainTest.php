<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Support\Testing\Fakes\BusFake;
use Maarheeze\Compass\Alerts\ConditionObservation;
use Maarheeze\Compass\Alerts\ConditionState;
use Maarheeze\Compass\Alerts\Jobs\HandleConditionObservations;
use Maarheeze\Compass\Alerts\Listeners\SiteReport\EvaluateSiteOnForgeDomain;
use Maarheeze\Compass\Alerts\Types\SiteOnForgeDomain;
use Maarheeze\Compass\Collector\Events\SiteInspected;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\Models\SiteReport;
use Psr\Clock\ClockInterface;
use Symfony\Component\Clock\MockClock;

test('handle observes on forge domain now for a site on the forge domain out of maintenance mode', function (): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(QueueingDispatcher::class, $busFake);
    $now = CarbonImmutable::parse(fake()->iso8601());
    $this->app->instance(ClockInterface::class, new MockClock($now));
    $siteReport = SiteReport::factory()
        ->for(Site::factory()->state(['url' => sprintf('https://%s.on-forge.com', fake()->slug())]))
        ->create(['maintenance_mode' => false]);

    $evaluateSiteOnForgeDomain = $this->app->make(EvaluateSiteOnForgeDomain::class);
    $evaluateSiteOnForgeDomain->handle(new SiteInspected($siteReport));

    $busFake->assertDispatched(
        HandleConditionObservations::class,
        static function (HandleConditionObservations $handleConditionObservations) use ($now, $siteReport): bool {
            return $handleConditionObservations->conditionObservations->contains(static function (ConditionObservation $conditionObservation) use ($now, $siteReport): bool {
                return $conditionObservation->alertType instanceof SiteOnForgeDomain
                    && $conditionObservation->subject === $siteReport
                    && $conditionObservation->condition === ConditionState::ACTIVE
                    && $conditionObservation->observedAt->equalTo($now);
            });
        },
    );
});

test('handle observes on forge domain resolved now for a site on the forge domain in maintenance mode', function (): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(QueueingDispatcher::class, $busFake);
    $now = CarbonImmutable::parse(fake()->iso8601());
    $this->app->instance(ClockInterface::class, new MockClock($now));
    $siteReport = SiteReport::factory()
        ->for(Site::factory()->state(['url' => sprintf('https://%s.on-forge.com', fake()->slug())]))
        ->create(['maintenance_mode' => true]);

    $evaluateSiteOnForgeDomain = $this->app->make(EvaluateSiteOnForgeDomain::class);
    $evaluateSiteOnForgeDomain->handle(new SiteInspected($siteReport));

    $busFake->assertDispatched(
        HandleConditionObservations::class,
        static function (HandleConditionObservations $handleConditionObservations) use ($now, $siteReport): bool {
            return $handleConditionObservations->conditionObservations->contains(static function (ConditionObservation $conditionObservation) use ($now, $siteReport): bool {
                return $conditionObservation->alertType instanceof SiteOnForgeDomain
                    && $conditionObservation->subject === $siteReport
                    && $conditionObservation->condition === ConditionState::RESOLVED
                    && $conditionObservation->observedAt->equalTo($now);
            });
        },
    );
});

test('handle observes on forge domain resolved now for a site on another domain', function (): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(QueueingDispatcher::class, $busFake);
    $now = CarbonImmutable::parse(fake()->iso8601());
    $this->app->instance(ClockInterface::class, new MockClock($now));
    $siteReport = SiteReport::factory()
        ->for(Site::factory()->state(['url' => sprintf('https://%s', fake()->domainName())]))
        ->create(['maintenance_mode' => false]);

    $evaluateSiteOnForgeDomain = $this->app->make(EvaluateSiteOnForgeDomain::class);
    $evaluateSiteOnForgeDomain->handle(new SiteInspected($siteReport));

    $busFake->assertDispatched(
        HandleConditionObservations::class,
        static function (HandleConditionObservations $handleConditionObservations) use ($now, $siteReport): bool {
            return $handleConditionObservations->conditionObservations->contains(static function (ConditionObservation $conditionObservation) use ($now, $siteReport): bool {
                return $conditionObservation->alertType instanceof SiteOnForgeDomain
                    && $conditionObservation->subject === $siteReport
                    && $conditionObservation->condition === ConditionState::RESOLVED
                    && $conditionObservation->observedAt->equalTo($now);
            });
        },
    );
});

test('handle observes nothing for a site not checked for maintenance mode', function (): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(QueueingDispatcher::class, $busFake);
    $siteReport = SiteReport::factory()->create(['maintenance_mode' => null]);

    $evaluateSiteOnForgeDomain = $this->app->make(EvaluateSiteOnForgeDomain::class);
    $evaluateSiteOnForgeDomain->handle(new SiteInspected($siteReport));

    $busFake->assertNotDispatched(HandleConditionObservations::class);
});
