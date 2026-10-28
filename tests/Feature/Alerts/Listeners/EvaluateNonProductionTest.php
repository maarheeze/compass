<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Testing\Fakes\EventFake;
use Maarheeze\Compass\Alerts\AlertCondition;
use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Alerts\Events\ConditionObserved;
use Maarheeze\Compass\Alerts\Listeners\EvaluateNonProduction;
use Maarheeze\Compass\Collector\Events\SiteInspected;
use Maarheeze\Compass\Models\Site;
use Psr\Clock\ClockInterface;
use Symfony\Component\Clock\MockClock;

test('handle observes non production now for a site in another environment', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));
    $this->app->instance(Dispatcher::class, $eventFake);
    $now = CarbonImmutable::parse(fake()->iso8601());
    $this->app->instance(ClockInterface::class, new MockClock($now));
    $site = Site::factory()->create([
        'app_env' => fake()->randomElement(['local', 'staging', 'testing']),
        'missing_at' => null,
    ]);

    $evaluateNonProduction = $this->app->make(EvaluateNonProduction::class);
    $evaluateNonProduction->handle(new SiteInspected($site));

    $eventFake->assertDispatched(ConditionObserved::class, static function (ConditionObserved $conditionObserved) use ($now, $site): bool {
        return $conditionObserved->alertType === AlertType::NON_PRODUCTION
            && $conditionObserved->subject === $site
            && $conditionObserved->condition === AlertCondition::PRESENT
            && $conditionObserved->observedAt->equalTo($now);
    });
});

test('handle observes non production gone now for a site in production or without an environment', function (?string $appEnv): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));
    $this->app->instance(Dispatcher::class, $eventFake);
    $now = CarbonImmutable::parse(fake()->iso8601());
    $this->app->instance(ClockInterface::class, new MockClock($now));
    $site = Site::factory()->create([
        'app_env' => $appEnv,
        'missing_at' => null,
    ]);

    $evaluateNonProduction = $this->app->make(EvaluateNonProduction::class);
    $evaluateNonProduction->handle(new SiteInspected($site));

    $eventFake->assertDispatched(ConditionObserved::class, static function (ConditionObserved $conditionObserved) use ($now, $site): bool {
        return $conditionObserved->alertType === AlertType::NON_PRODUCTION
            && $conditionObserved->subject === $site
            && $conditionObserved->condition === AlertCondition::GONE
            && $conditionObserved->observedAt->equalTo($now);
    });
})->with(['production', null]);

test('handle observes non production gone for a missing site in another environment', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));
    $this->app->instance(Dispatcher::class, $eventFake);
    $site = Site::factory()->create([
        'app_env' => fake()->randomElement(['local', 'staging', 'testing']),
        'missing_at' => fake()->dateTime(),
    ]);

    $evaluateNonProduction = $this->app->make(EvaluateNonProduction::class);
    $evaluateNonProduction->handle(new SiteInspected($site));

    $eventFake->assertDispatched(ConditionObserved::class, static function (ConditionObserved $conditionObserved): bool {
        return $conditionObserved->condition === AlertCondition::GONE;
    });
});
