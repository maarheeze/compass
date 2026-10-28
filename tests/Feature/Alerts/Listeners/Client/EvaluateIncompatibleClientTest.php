<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Testing\Fakes\EventFake;
use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Alerts\ConditionState;
use Maarheeze\Compass\Alerts\Events\ConditionObserved;
use Maarheeze\Compass\Alerts\Listeners\Client\EvaluateIncompatibleClient;
use Maarheeze\Compass\Collector\Client\ClientStatus;
use Maarheeze\Compass\Collector\Events\ForgeRunSucceeded;
use Maarheeze\Compass\Collector\Events\SiteInspected;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Uuid\Uuid;
use Psr\Clock\ClockInterface;
use Symfony\Component\Clock\MockClock;

test('handle observes incompatible client now for a site with an outdated client', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));
    $this->app->instance(Dispatcher::class, $eventFake);
    $now = CarbonImmutable::parse(fake()->iso8601());
    $this->app->instance(ClockInterface::class, new MockClock($now));
    $site = Site::factory()->create([
        'client_status' => ClientStatus::INCOMPATIBLE,
        'missing_at' => null,
    ]);

    $evaluateIncompatibleClient = $this->app->make(EvaluateIncompatibleClient::class);
    $evaluateIncompatibleClient->handle(new SiteInspected($site));

    $eventFake->assertDispatched(ConditionObserved::class, static function (ConditionObserved $conditionObserved) use ($now, $site): bool {
        return $conditionObserved->alertType === AlertType::INCOMPATIBLE_CLIENT
            && $conditionObserved->subject === $site
            && $conditionObserved->condition === ConditionState::ACTIVE
            && $conditionObserved->observedAt->equalTo($now);
    });
});

test('handle observes incompatible client resolved now for a site with any other client status', function (?ClientStatus $clientStatus): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));
    $this->app->instance(Dispatcher::class, $eventFake);
    $now = CarbonImmutable::parse(fake()->iso8601());
    $this->app->instance(ClockInterface::class, new MockClock($now));
    $site = Site::factory()->create([
        'client_status' => $clientStatus,
        'missing_at' => null,
    ]);

    $evaluateIncompatibleClient = $this->app->make(EvaluateIncompatibleClient::class);
    $evaluateIncompatibleClient->handle(new SiteInspected($site));

    $eventFake->assertDispatched(ConditionObserved::class, static function (ConditionObserved $conditionObserved) use ($now, $site): bool {
        return $conditionObserved->alertType === AlertType::INCOMPATIBLE_CLIENT
            && $conditionObserved->subject === $site
            && $conditionObserved->condition === ConditionState::RESOLVED
            && $conditionObserved->observedAt->equalTo($now);
    });
})->with([
    ClientStatus::INSTALLED,
    ClientStatus::NOT_INSTALLED,
    ClientStatus::UNAUTHORIZED,
    ClientStatus::UNREACHABLE,
    null,
]);

test('handle observes incompatible client resolved for a missing site with an outdated client', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));
    $this->app->instance(Dispatcher::class, $eventFake);
    $site = Site::factory()->create([
        'client_status' => ClientStatus::INCOMPATIBLE,
        'missing_at' => fake()->dateTime(),
    ]);

    $evaluateIncompatibleClient = $this->app->make(EvaluateIncompatibleClient::class);
    $evaluateIncompatibleClient->handle(new SiteInspected($site));

    $eventFake->assertDispatched(ConditionObserved::class, static function (ConditionObserved $conditionObserved): bool {
        return $conditionObserved->condition === ConditionState::RESOLVED;
    });
});

test('handle observes incompatible client resolved now for a missing site after a forge run', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));
    $this->app->instance(Dispatcher::class, $eventFake);
    $now = CarbonImmutable::parse(fake()->iso8601());
    $this->app->instance(ClockInterface::class, new MockClock($now));
    $site = Site::factory()->create(['missing_at' => fake()->dateTime()]);

    $evaluateIncompatibleClient = $this->app->make(EvaluateIncompatibleClient::class);
    $evaluateIncompatibleClient->handle(new ForgeRunSucceeded(Uuid::generate()));

    $eventFake->assertDispatched(ConditionObserved::class, static function (ConditionObserved $conditionObserved) use ($now, $site): bool {
        return $conditionObserved->alertType === AlertType::INCOMPATIBLE_CLIENT
            && $conditionObserved->subject instanceof Site
            && $conditionObserved->subject->is($site)
            && $conditionObserved->condition === ConditionState::RESOLVED
            && $conditionObserved->observedAt->equalTo($now);
    });
});

test('handle observes nothing for a collected site after a forge run', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));
    $this->app->instance(Dispatcher::class, $eventFake);
    Site::factory()->create(['missing_at' => null]);

    $evaluateIncompatibleClient = $this->app->make(EvaluateIncompatibleClient::class);
    $evaluateIncompatibleClient->handle(new ForgeRunSucceeded(Uuid::generate()));

    $eventFake->assertNothingDispatched();
});
