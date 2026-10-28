<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Testing\Fakes\EventFake;
use Maarheeze\Compass\Alerts\AlertCondition;
use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Alerts\Events\ConditionObserved;
use Maarheeze\Compass\Alerts\Listeners\EvaluateDownSiteEndpoint;
use Maarheeze\Compass\Collector\Events\ForgeRunSucceeded;
use Maarheeze\Compass\Collector\Events\SiteEndpointChecked;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\Models\SiteEndpoint;
use Maarheeze\Compass\Models\SiteEndpointCheck;
use Maarheeze\Uuid\Uuid;
use Psr\Clock\ClockInterface;
use Symfony\Component\Clock\MockClock;

test('handle observes the endpoint down at the check for a failed check', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));
    $this->app->instance(Dispatcher::class, $eventFake);
    $siteEndpoint = SiteEndpoint::factory()->create();
    $siteEndpointCheck = SiteEndpointCheck::factory()->create(['is_successful' => false]);

    $evaluateDownSiteEndpoint = $this->app->make(EvaluateDownSiteEndpoint::class);
    $evaluateDownSiteEndpoint->handle(new SiteEndpointChecked($siteEndpoint, $siteEndpointCheck));

    $eventFake->assertDispatched(ConditionObserved::class, static function (ConditionObserved $conditionObserved) use ($siteEndpoint, $siteEndpointCheck): bool {
        return $conditionObserved->alertType === AlertType::DOWN_SITE_ENDPOINT
            && $conditionObserved->subject === $siteEndpoint
            && $conditionObserved->condition === AlertCondition::PRESENT
            && $conditionObserved->observedAt->equalTo($siteEndpointCheck->created_at);
    });
});

test('handle observes the endpoint no longer down at the check for a successful check', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));
    $this->app->instance(Dispatcher::class, $eventFake);
    $siteEndpoint = SiteEndpoint::factory()->create();
    $siteEndpointCheck = SiteEndpointCheck::factory()->create(['is_successful' => true]);

    $evaluateDownSiteEndpoint = $this->app->make(EvaluateDownSiteEndpoint::class);
    $evaluateDownSiteEndpoint->handle(new SiteEndpointChecked($siteEndpoint, $siteEndpointCheck));

    $eventFake->assertDispatched(ConditionObserved::class, static function (ConditionObserved $conditionObserved) use ($siteEndpoint, $siteEndpointCheck): bool {
        return $conditionObserved->alertType === AlertType::DOWN_SITE_ENDPOINT
            && $conditionObserved->subject === $siteEndpoint
            && $conditionObserved->condition === AlertCondition::GONE
            && $conditionObserved->observedAt->equalTo($siteEndpointCheck->created_at);
    });
});

test('handle observes the endpoint of a missing site no longer down now', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));
    $this->app->instance(Dispatcher::class, $eventFake);
    $now = CarbonImmutable::parse(fake()->iso8601());
    $this->app->instance(ClockInterface::class, new MockClock($now));
    $siteEndpoint = SiteEndpoint::factory()
        ->for(Site::factory()->state(['missing_at' => fake()->dateTime()]))
        ->create();

    $evaluateDownSiteEndpoint = $this->app->make(EvaluateDownSiteEndpoint::class);
    $evaluateDownSiteEndpoint->handle(new ForgeRunSucceeded(Uuid::generate()));

    $eventFake->assertDispatched(ConditionObserved::class, static function (ConditionObserved $conditionObserved) use ($now, $siteEndpoint): bool {
        return $conditionObserved->alertType === AlertType::DOWN_SITE_ENDPOINT
            && $conditionObserved->subject instanceof SiteEndpoint
            && $conditionObserved->subject->is($siteEndpoint)
            && $conditionObserved->condition === AlertCondition::GONE
            && $conditionObserved->observedAt->equalTo($now);
    });
});

test('handle observes nothing for the endpoint of a collected site after a forge run', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));
    $this->app->instance(Dispatcher::class, $eventFake);
    SiteEndpoint::factory()
        ->for(Site::factory()->state(['missing_at' => null]))
        ->create();

    $evaluateDownSiteEndpoint = $this->app->make(EvaluateDownSiteEndpoint::class);
    $evaluateDownSiteEndpoint->handle(new ForgeRunSucceeded(Uuid::generate()));

    $eventFake->assertNothingDispatched();
});
