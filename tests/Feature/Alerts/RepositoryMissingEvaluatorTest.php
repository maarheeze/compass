<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Maarheeze\Compass\Alerts\ConditionState;
use Maarheeze\Compass\Alerts\RepositoryMissingEvaluator;
use Maarheeze\Compass\Alerts\Types\RepositoryMissing;
use Maarheeze\Compass\Models\Repository;
use Psr\Clock\ClockInterface;
use Symfony\Component\Clock\MockClock;

test('conditionObservation observes a missing, not ignored repository missing now', function (): void {
    $now = CarbonImmutable::parse(fake()->iso8601());
    $this->app->instance(ClockInterface::class, new MockClock($now));
    $repository = Repository::factory()->create([
        'missing_at' => fake()->dateTime(),
        'missing_ignored_at' => null,
    ]);

    $repositoryMissingEvaluator = $this->app->make(RepositoryMissingEvaluator::class);
    $conditionObservation = $repositoryMissingEvaluator->conditionObservation($repository);

    expect($conditionObservation->alertType)
        ->toBeInstanceOf(RepositoryMissing::class)
        ->and($conditionObservation->subject)
        ->toBe($repository)
        ->and($conditionObservation->condition)
        ->toBe(ConditionState::ACTIVE)
        ->and($conditionObservation->observedAt->equalTo($now))
        ->toBeTrue();
});

test('conditionObservation observes a collected repository no longer missing now', function (): void {
    $now = CarbonImmutable::parse(fake()->iso8601());
    $this->app->instance(ClockInterface::class, new MockClock($now));
    $repository = Repository::factory()->create([
        'missing_at' => null,
        'missing_ignored_at' => null,
    ]);

    $repositoryMissingEvaluator = $this->app->make(RepositoryMissingEvaluator::class);
    $conditionObservation = $repositoryMissingEvaluator->conditionObservation($repository);

    expect($conditionObservation->alertType)
        ->toBeInstanceOf(RepositoryMissing::class)
        ->and($conditionObservation->subject)
        ->toBe($repository)
        ->and($conditionObservation->condition)
        ->toBe(ConditionState::RESOLVED)
        ->and($conditionObservation->observedAt->equalTo($now))
        ->toBeTrue();
});

test('conditionObservation observes a missing, ignored repository no longer missing', function (): void {
    $repository = Repository::factory()->create([
        'missing_at' => fake()->dateTimeBetween('-2 months', '-1 month'),
        'missing_ignored_at' => fake()->dateTimeBetween('-1 month'),
    ]);

    $repositoryMissingEvaluator = $this->app->make(RepositoryMissingEvaluator::class);
    $conditionObservation = $repositoryMissingEvaluator->conditionObservation($repository);

    expect($conditionObservation->condition)
        ->toBe(ConditionState::RESOLVED);
});
