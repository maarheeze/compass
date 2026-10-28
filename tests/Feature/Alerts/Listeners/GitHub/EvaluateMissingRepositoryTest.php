<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Testing\Fakes\EventFake;
use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Alerts\ConditionState;
use Maarheeze\Compass\Alerts\Events\ConditionObserved;
use Maarheeze\Compass\Alerts\Listeners\GitHub\EvaluateMissingRepository;
use Maarheeze\Compass\Collector\Events\GitHubRunSucceeded;
use Maarheeze\Compass\Collector\Events\RepositoryMissingIgnored;
use Maarheeze\Compass\Models\Repository;
use Maarheeze\Uuid\Uuid;
use Psr\Clock\ClockInterface;
use Symfony\Component\Clock\MockClock;

test('handle observes a missing, not ignored repository missing now', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));
    $this->app->instance(Dispatcher::class, $eventFake);
    $now = CarbonImmutable::parse(fake()->iso8601());
    $this->app->instance(ClockInterface::class, new MockClock($now));
    $repository = Repository::factory()->create([
        'missing_at' => fake()->dateTime(),
        'missing_ignored_at' => null,
    ]);

    $evaluateMissingRepository = $this->app->make(EvaluateMissingRepository::class);
    $evaluateMissingRepository->handle(new GitHubRunSucceeded(Uuid::generate()));

    $eventFake->assertDispatched(ConditionObserved::class, static function (ConditionObserved $conditionObserved) use ($now, $repository): bool {
        return $conditionObserved->alertType === AlertType::MISSING_REPOSITORY
            && $conditionObserved->subject instanceof Repository
            && $conditionObserved->subject->is($repository)
            && $conditionObserved->condition === ConditionState::ACTIVE
            && $conditionObserved->observedAt->equalTo($now);
    });
});

test('handle observes a collected repository no longer missing now', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));
    $this->app->instance(Dispatcher::class, $eventFake);
    $now = CarbonImmutable::parse(fake()->iso8601());
    $this->app->instance(ClockInterface::class, new MockClock($now));
    $repository = Repository::factory()->create([
        'missing_at' => null,
        'missing_ignored_at' => null,
    ]);

    $evaluateMissingRepository = $this->app->make(EvaluateMissingRepository::class);
    $evaluateMissingRepository->handle(new GitHubRunSucceeded(Uuid::generate()));

    $eventFake->assertDispatched(ConditionObserved::class, static function (ConditionObserved $conditionObserved) use ($now, $repository): bool {
        return $conditionObserved->alertType === AlertType::MISSING_REPOSITORY
            && $conditionObserved->subject instanceof Repository
            && $conditionObserved->subject->is($repository)
            && $conditionObserved->condition === ConditionState::RESOLVED
            && $conditionObserved->observedAt->equalTo($now);
    });
});

test('handle observes a missing, ignored repository no longer missing', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));
    $this->app->instance(Dispatcher::class, $eventFake);
    $repository = Repository::factory()->create([
        'missing_at' => fake()->dateTimeBetween('-2 months', '-1 month'),
        'missing_ignored_at' => fake()->dateTimeBetween('-1 month'),
    ]);

    $evaluateMissingRepository = $this->app->make(EvaluateMissingRepository::class);
    $evaluateMissingRepository->handle(new GitHubRunSucceeded(Uuid::generate()));

    $eventFake->assertDispatched(ConditionObserved::class, static function (ConditionObserved $conditionObserved) use ($repository): bool {
        return $conditionObserved->subject instanceof Repository
            && $conditionObserved->subject->is($repository)
            && $conditionObserved->condition === ConditionState::RESOLVED;
    });
});

test('handle observes an ignored repository no longer missing now', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));
    $this->app->instance(Dispatcher::class, $eventFake);
    $now = CarbonImmutable::parse(fake()->iso8601());
    $this->app->instance(ClockInterface::class, new MockClock($now));
    $repository = Repository::factory()->create([
        'missing_at' => fake()->dateTimeBetween('-2 months', '-1 month'),
        'missing_ignored_at' => fake()->dateTimeBetween('-1 month'),
    ]);

    $evaluateMissingRepository = $this->app->make(EvaluateMissingRepository::class);
    $evaluateMissingRepository->handle(new RepositoryMissingIgnored($repository));

    $eventFake->assertDispatched(ConditionObserved::class, static function (ConditionObserved $conditionObserved) use ($now, $repository): bool {
        return $conditionObserved->alertType === AlertType::MISSING_REPOSITORY
            && $conditionObserved->subject === $repository
            && $conditionObserved->condition === ConditionState::RESOLVED
            && $conditionObserved->observedAt->equalTo($now);
    });
});
