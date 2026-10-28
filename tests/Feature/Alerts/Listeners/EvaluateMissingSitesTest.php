<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Testing\Fakes\EventFake;
use Maarheeze\Compass\Alerts\AlertCondition;
use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Alerts\Events\ConditionObserved;
use Maarheeze\Compass\Alerts\Listeners\EvaluateMissingSites;
use Maarheeze\Compass\Collector\Events\ForgeRunSucceeded;
use Maarheeze\Compass\Collector\Events\SiteMissingIgnored;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Uuid\Uuid;
use Psr\Clock\ClockInterface;
use Symfony\Component\Clock\MockClock;

test('handle observes a missing, not ignored site missing now', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));
    $this->app->instance(Dispatcher::class, $eventFake);
    $now = CarbonImmutable::parse(fake()->iso8601());
    $this->app->instance(ClockInterface::class, new MockClock($now));
    $site = Site::factory()->create([
        'missing_at' => fake()->dateTime(),
        'missing_ignored_at' => null,
    ]);

    $evaluateMissingSites = $this->app->make(EvaluateMissingSites::class);
    $evaluateMissingSites->handle(new ForgeRunSucceeded(Uuid::generate()));

    $eventFake->assertDispatched(ConditionObserved::class, static function (ConditionObserved $conditionObserved) use ($now, $site): bool {
        return $conditionObserved->alertType === AlertType::MISSING_SITE
            && $conditionObserved->subject instanceof Site
            && $conditionObserved->subject->is($site)
            && $conditionObserved->condition === AlertCondition::PRESENT
            && $conditionObserved->observedAt->equalTo($now);
    });
});

test('handle observes a collected site no longer missing now', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));
    $this->app->instance(Dispatcher::class, $eventFake);
    $now = CarbonImmutable::parse(fake()->iso8601());
    $this->app->instance(ClockInterface::class, new MockClock($now));
    $site = Site::factory()->create([
        'missing_at' => null,
        'missing_ignored_at' => null,
    ]);

    $evaluateMissingSites = $this->app->make(EvaluateMissingSites::class);
    $evaluateMissingSites->handle(new ForgeRunSucceeded(Uuid::generate()));

    $eventFake->assertDispatched(ConditionObserved::class, static function (ConditionObserved $conditionObserved) use ($now, $site): bool {
        return $conditionObserved->alertType === AlertType::MISSING_SITE
            && $conditionObserved->subject instanceof Site
            && $conditionObserved->subject->is($site)
            && $conditionObserved->condition === AlertCondition::GONE
            && $conditionObserved->observedAt->equalTo($now);
    });
});

test('handle observes a missing, ignored site no longer missing', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));
    $this->app->instance(Dispatcher::class, $eventFake);
    $site = Site::factory()->create([
        'missing_at' => fake()->dateTimeBetween('-2 months', '-1 month'),
        'missing_ignored_at' => fake()->dateTimeBetween('-1 month'),
    ]);

    $evaluateMissingSites = $this->app->make(EvaluateMissingSites::class);
    $evaluateMissingSites->handle(new ForgeRunSucceeded(Uuid::generate()));

    $eventFake->assertDispatched(ConditionObserved::class, static function (ConditionObserved $conditionObserved) use ($site): bool {
        return $conditionObserved->subject instanceof Site
            && $conditionObserved->subject->is($site)
            && $conditionObserved->condition === AlertCondition::GONE;
    });
});

test('handle observes an ignored site no longer missing now', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));
    $this->app->instance(Dispatcher::class, $eventFake);
    $now = CarbonImmutable::parse(fake()->iso8601());
    $this->app->instance(ClockInterface::class, new MockClock($now));
    $site = Site::factory()->create([
        'missing_at' => fake()->dateTimeBetween('-2 months', '-1 month'),
        'missing_ignored_at' => fake()->dateTimeBetween('-1 month'),
    ]);

    $evaluateMissingSites = $this->app->make(EvaluateMissingSites::class);
    $evaluateMissingSites->handle(new SiteMissingIgnored($site));

    $eventFake->assertDispatched(ConditionObserved::class, static function (ConditionObserved $conditionObserved) use ($now, $site): bool {
        return $conditionObserved->alertType === AlertType::MISSING_SITE
            && $conditionObserved->subject === $site
            && $conditionObserved->condition === AlertCondition::GONE
            && $conditionObserved->observedAt->equalTo($now);
    });
});
