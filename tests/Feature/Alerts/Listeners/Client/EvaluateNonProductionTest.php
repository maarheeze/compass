<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Testing\Fakes\EventFake;
use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Alerts\ConditionState;
use Maarheeze\Compass\Alerts\Events\ConditionObserved;
use Maarheeze\Compass\Alerts\Listeners\Client\EvaluateNonProduction;
use Maarheeze\Compass\Collector\Events\SiteInspected;
use Maarheeze\Compass\Models\Site;
use Psr\Clock\ClockInterface;
use Symfony\Component\Clock\MockClock;

test('handle observes non production now for a site that is not in production', function (?string $appEnv): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));
    $this->app->instance(Dispatcher::class, $eventFake);
    $now = CarbonImmutable::parse(fake()->iso8601());
    $this->app->instance(ClockInterface::class, new MockClock($now));
    $site = Site::factory()->create(['app_env' => $appEnv]);

    $evaluateNonProduction = $this->app->make(EvaluateNonProduction::class);
    $evaluateNonProduction->handle(new SiteInspected($site));

    $eventFake->assertDispatched(ConditionObserved::class, static function (ConditionObserved $conditionObserved) use ($now, $site): bool {
        return $conditionObserved->alertType === AlertType::NON_PRODUCTION
            && $conditionObserved->subject === $site
            && $conditionObserved->condition === ConditionState::ACTIVE
            && $conditionObserved->observedAt->equalTo($now);
    });
})->with(['local', 'staging', 'testing', null]);

test('handle observes non production resolved now for a site in production', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));
    $this->app->instance(Dispatcher::class, $eventFake);
    $now = CarbonImmutable::parse(fake()->iso8601());
    $this->app->instance(ClockInterface::class, new MockClock($now));
    $site = Site::factory()->create(['app_env' => 'production']);

    $evaluateNonProduction = $this->app->make(EvaluateNonProduction::class);
    $evaluateNonProduction->handle(new SiteInspected($site));

    $eventFake->assertDispatched(ConditionObserved::class, static function (ConditionObserved $conditionObserved) use ($now, $site): bool {
        return $conditionObserved->alertType === AlertType::NON_PRODUCTION
            && $conditionObserved->subject === $site
            && $conditionObserved->condition === ConditionState::RESOLVED
            && $conditionObserved->observedAt->equalTo($now);
    });
});
