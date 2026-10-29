<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Testing\Fakes\EventFake;
use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Alerts\ConditionState;
use Maarheeze\Compass\Alerts\Events\ConditionObserved;
use Maarheeze\Compass\Alerts\Listeners\Client\EvaluateDebugEnabled;
use Maarheeze\Compass\Collector\Events\SiteInspected;
use Maarheeze\Compass\Models\Site;
use Psr\Clock\ClockInterface;
use Symfony\Component\Clock\MockClock;

test('handle observes debug enabled now for a site in debug mode', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));
    $this->app->instance(Dispatcher::class, $eventFake);
    $now = CarbonImmutable::parse(fake()->iso8601());
    $this->app->instance(ClockInterface::class, new MockClock($now));
    $site = Site::factory()->create([
        'app_debug' => true,
        'missing_at' => null,
    ]);

    $evaluateDebugEnabled = $this->app->make(EvaluateDebugEnabled::class);
    $evaluateDebugEnabled->handle(new SiteInspected($site));

    $eventFake->assertDispatched(ConditionObserved::class, static function (ConditionObserved $conditionObserved) use ($now, $site): bool {
        return $conditionObserved->alertType === AlertType::DEBUG_ENABLED
            && $conditionObserved->subject === $site
            && $conditionObserved->condition === ConditionState::ACTIVE
            && $conditionObserved->observedAt->equalTo($now);
    });
});

test('handle observes debug enabled resolved now for a site out of debug mode', function (?bool $appDebug): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));
    $this->app->instance(Dispatcher::class, $eventFake);
    $now = CarbonImmutable::parse(fake()->iso8601());
    $this->app->instance(ClockInterface::class, new MockClock($now));
    $site = Site::factory()->create([
        'app_debug' => $appDebug,
        'missing_at' => null,
    ]);

    $evaluateDebugEnabled = $this->app->make(EvaluateDebugEnabled::class);
    $evaluateDebugEnabled->handle(new SiteInspected($site));

    $eventFake->assertDispatched(ConditionObserved::class, static function (ConditionObserved $conditionObserved) use ($now, $site): bool {
        return $conditionObserved->alertType === AlertType::DEBUG_ENABLED
            && $conditionObserved->subject === $site
            && $conditionObserved->condition === ConditionState::RESOLVED
            && $conditionObserved->observedAt->equalTo($now);
    });
})->with([false, null]);

test('handle observes debug enabled resolved for a missing site in debug mode', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));
    $this->app->instance(Dispatcher::class, $eventFake);
    $site = Site::factory()->create([
        'app_debug' => true,
        'missing_at' => fake()->dateTime(),
    ]);

    $evaluateDebugEnabled = $this->app->make(EvaluateDebugEnabled::class);
    $evaluateDebugEnabled->handle(new SiteInspected($site));

    $eventFake->assertDispatched(ConditionObserved::class, static function (ConditionObserved $conditionObserved): bool {
        return $conditionObserved->condition === ConditionState::RESOLVED;
    });
});
