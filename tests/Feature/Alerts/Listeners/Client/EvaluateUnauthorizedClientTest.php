<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Testing\Fakes\EventFake;
use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Alerts\ConditionState;
use Maarheeze\Compass\Alerts\Events\ConditionObserved;
use Maarheeze\Compass\Alerts\Listeners\Client\EvaluateUnauthorizedClient;
use Maarheeze\Compass\Collector\Client\ClientStatus;
use Maarheeze\Compass\Collector\Events\ForgeRunSucceeded;
use Maarheeze\Compass\Collector\Events\SiteInspected;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Uuid\Uuid;
use Psr\Clock\ClockInterface;
use Symfony\Component\Clock\MockClock;

test('handle observes unauthorized client now for a site that refused the key', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));
    $this->app->instance(Dispatcher::class, $eventFake);
    $now = CarbonImmutable::parse(fake()->iso8601());
    $this->app->instance(ClockInterface::class, new MockClock($now));
    $site = Site::factory()->create([
        'client_status' => ClientStatus::UNAUTHORIZED,
        'missing_at' => null,
    ]);

    $evaluateUnauthorizedClient = $this->app->make(EvaluateUnauthorizedClient::class);
    $evaluateUnauthorizedClient->handle(new SiteInspected($site));

    $eventFake->assertDispatched(ConditionObserved::class, static function (ConditionObserved $conditionObserved) use ($now, $site): bool {
        return $conditionObserved->alertType === AlertType::UNAUTHORIZED_CLIENT
            && $conditionObserved->subject === $site
            && $conditionObserved->condition === ConditionState::PRESENT
            && $conditionObserved->observedAt->equalTo($now);
    });
});

test('handle observes unauthorized client gone now for a site with any other client status', function (?ClientStatus $clientStatus): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));
    $this->app->instance(Dispatcher::class, $eventFake);
    $now = CarbonImmutable::parse(fake()->iso8601());
    $this->app->instance(ClockInterface::class, new MockClock($now));
    $site = Site::factory()->create([
        'client_status' => $clientStatus,
        'missing_at' => null,
    ]);

    $evaluateUnauthorizedClient = $this->app->make(EvaluateUnauthorizedClient::class);
    $evaluateUnauthorizedClient->handle(new SiteInspected($site));

    $eventFake->assertDispatched(ConditionObserved::class, static function (ConditionObserved $conditionObserved) use ($now, $site): bool {
        return $conditionObserved->alertType === AlertType::UNAUTHORIZED_CLIENT
            && $conditionObserved->subject === $site
            && $conditionObserved->condition === ConditionState::GONE
            && $conditionObserved->observedAt->equalTo($now);
    });
})->with([
    ClientStatus::INCOMPATIBLE,
    ClientStatus::INSTALLED,
    ClientStatus::NOT_INSTALLED,
    ClientStatus::UNREACHABLE,
    null,
]);

test('handle observes unauthorized client gone for a missing site that refused the key', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));
    $this->app->instance(Dispatcher::class, $eventFake);
    $site = Site::factory()->create([
        'client_status' => ClientStatus::UNAUTHORIZED,
        'missing_at' => fake()->dateTime(),
    ]);

    $evaluateUnauthorizedClient = $this->app->make(EvaluateUnauthorizedClient::class);
    $evaluateUnauthorizedClient->handle(new SiteInspected($site));

    $eventFake->assertDispatched(ConditionObserved::class, static function (ConditionObserved $conditionObserved): bool {
        return $conditionObserved->condition === ConditionState::GONE;
    });
});

test('handle observes unauthorized client gone now for a missing site after a forge run', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));
    $this->app->instance(Dispatcher::class, $eventFake);
    $now = CarbonImmutable::parse(fake()->iso8601());
    $this->app->instance(ClockInterface::class, new MockClock($now));
    $site = Site::factory()->create(['missing_at' => fake()->dateTime()]);

    $evaluateUnauthorizedClient = $this->app->make(EvaluateUnauthorizedClient::class);
    $evaluateUnauthorizedClient->handle(new ForgeRunSucceeded(Uuid::generate()));

    $eventFake->assertDispatched(ConditionObserved::class, static function (ConditionObserved $conditionObserved) use ($now, $site): bool {
        return $conditionObserved->alertType === AlertType::UNAUTHORIZED_CLIENT
            && $conditionObserved->subject instanceof Site
            && $conditionObserved->subject->is($site)
            && $conditionObserved->condition === ConditionState::GONE
            && $conditionObserved->observedAt->equalTo($now);
    });
});

test('handle observes nothing for a collected site after a forge run', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));
    $this->app->instance(Dispatcher::class, $eventFake);
    Site::factory()->create(['missing_at' => null]);

    $evaluateUnauthorizedClient = $this->app->make(EvaluateUnauthorizedClient::class);
    $evaluateUnauthorizedClient->handle(new ForgeRunSucceeded(Uuid::generate()));

    $eventFake->assertNothingDispatched();
});
