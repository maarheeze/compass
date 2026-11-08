<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Maarheeze\Compass\Alerts\ComposerWarningEvaluator;
use Maarheeze\Compass\Alerts\ConditionObservation;
use Maarheeze\Compass\Alerts\ConditionState;
use Maarheeze\Compass\Alerts\Types\ComposerLibraryWithLock;
use Maarheeze\Compass\Alerts\Types\ComposerProjectWithoutLock;
use Maarheeze\Compass\Alerts\Types\ComposerTypeMissing;
use Maarheeze\Compass\Alerts\Types\ComposerTypeUnknown;
use Maarheeze\Compass\Models\ComposerJson;
use Maarheeze\Compass\Models\Repository;
use Psr\Clock\ClockInterface;
use Symfony\Component\Clock\MockClock;

test('conditionObservations observes the detected composer warning active now', function (): void {
    $now = CarbonImmutable::parse(fake()->iso8601());
    $this->app->instance(ClockInterface::class, new MockClock($now));
    $repository = Repository::factory()->create([
        'composer_json_version' => fake()->sha1(),
        'composer_lock_version' => null,
    ]);
    ComposerJson::factory()->create([
        'repository_id' => $repository->id,
        'type' => 'project',
    ]);

    $composerWarningEvaluator = $this->app->make(ComposerWarningEvaluator::class);
    $conditionObservations = $composerWarningEvaluator->conditionObservations($repository);

    $hasActiveObservation = $conditionObservations->contains(static function (ConditionObservation $conditionObservation) use ($now, $repository): bool {
        return $conditionObservation->alertType instanceof ComposerProjectWithoutLock
            && $conditionObservation->subject === $repository
            && $conditionObservation->condition === ConditionState::ACTIVE
            && $conditionObservation->observedAt->equalTo($now);
    });
    expect($hasActiveObservation)
        ->toBeTrue();
});

test('conditionObservations observes the composer warnings that are not detected resolved now', function (string $alertTypeClass): void {
    $now = CarbonImmutable::parse(fake()->iso8601());
    $this->app->instance(ClockInterface::class, new MockClock($now));
    $repository = Repository::factory()->create([
        'composer_json_version' => fake()->sha1(),
        'composer_lock_version' => null,
    ]);
    ComposerJson::factory()->create([
        'repository_id' => $repository->id,
        'type' => 'project',
    ]);

    $composerWarningEvaluator = $this->app->make(ComposerWarningEvaluator::class);
    $conditionObservations = $composerWarningEvaluator->conditionObservations($repository);

    $hasResolvedObservation = $conditionObservations->contains(static function (ConditionObservation $conditionObservation) use ($alertTypeClass, $now, $repository): bool {
        return $conditionObservation->alertType instanceof $alertTypeClass
            && $conditionObservation->subject === $repository
            && $conditionObservation->condition === ConditionState::RESOLVED
            && $conditionObservation->observedAt->equalTo($now);
    });
    expect($hasResolvedObservation)
        ->toBeTrue();
})->with([
    ComposerLibraryWithLock::class,
    ComposerTypeMissing::class,
    ComposerTypeUnknown::class,
]);

test('conditionObservations observes every composer warning resolved now for a repository without a composer warning', function (string $alertTypeClass): void {
    $now = CarbonImmutable::parse(fake()->iso8601());
    $this->app->instance(ClockInterface::class, new MockClock($now));
    $repository = Repository::factory()->create(['composer_json_version' => null]);

    $composerWarningEvaluator = $this->app->make(ComposerWarningEvaluator::class);
    $conditionObservations = $composerWarningEvaluator->conditionObservations($repository);

    $hasResolvedObservation = $conditionObservations->contains(static function (ConditionObservation $conditionObservation) use ($alertTypeClass, $now, $repository): bool {
        return $conditionObservation->alertType instanceof $alertTypeClass
            && $conditionObservation->subject === $repository
            && $conditionObservation->condition === ConditionState::RESOLVED
            && $conditionObservation->observedAt->equalTo($now);
    });
    expect($hasResolvedObservation)
        ->toBeTrue();
})->with([
    ComposerLibraryWithLock::class,
    ComposerTypeMissing::class,
    ComposerProjectWithoutLock::class,
    ComposerTypeUnknown::class,
]);

test('conditionObservations judges the composer json stored now, not one loaded before', function (): void {
    $repository = Repository::factory()->create([
        'composer_json_version' => fake()->sha1(),
        'composer_lock_version' => null,
    ]);
    $repository->load('composerJson');
    ComposerJson::factory()->create([
        'repository_id' => $repository->id,
        'type' => 'project',
    ]);

    $composerWarningEvaluator = $this->app->make(ComposerWarningEvaluator::class);
    $conditionObservations = $composerWarningEvaluator->conditionObservations($repository);

    $hasActiveObservation = $conditionObservations->contains(static function (ConditionObservation $conditionObservation): bool {
        return $conditionObservation->alertType instanceof ComposerProjectWithoutLock
            && $conditionObservation->condition === ConditionState::ACTIVE;
    });
    expect($hasActiveObservation)
        ->toBeTrue();
});
