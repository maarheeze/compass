<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Alerts\ComposerWarningEvaluator;
use Maarheeze\Compass\Alerts\ConditionObservation;
use Maarheeze\Compass\Alerts\ConditionState;
use Maarheeze\Compass\Models\ComposerJson;
use Maarheeze\Compass\Models\Repository;
use Psr\Clock\ClockInterface;
use Symfony\Component\Clock\MockClock;

test('conditionObservations observes the detected composer warning active now', function (): void {
    $now = CarbonImmutable::parse(fake()->iso8601());
    $this->app->instance(ClockInterface::class, new MockClock($now));
    $repository = Repository::factory()->create([
        'composer_json_oid' => fake()->sha1(),
        'composer_lock_oid' => null,
    ]);
    ComposerJson::factory()->create([
        'repository_id' => $repository->id,
        'type' => 'project',
    ]);

    $composerWarningEvaluator = $this->app->make(ComposerWarningEvaluator::class);
    $conditionObservations = $composerWarningEvaluator->conditionObservations($repository);

    $hasActiveObservation = $conditionObservations->contains(static function (ConditionObservation $conditionObservation) use ($now, $repository): bool {
        return $conditionObservation->alertType === AlertType::COMPOSER_PROJECT_WITHOUT_LOCK
            && $conditionObservation->subject === $repository
            && $conditionObservation->condition === ConditionState::ACTIVE
            && $conditionObservation->observedAt->equalTo($now);
    });
    expect($hasActiveObservation)
        ->toBeTrue();
});

test('conditionObservations observes the composer warnings that are not detected resolved now', function (AlertType $alertType): void {
    $now = CarbonImmutable::parse(fake()->iso8601());
    $this->app->instance(ClockInterface::class, new MockClock($now));
    $repository = Repository::factory()->create([
        'composer_json_oid' => fake()->sha1(),
        'composer_lock_oid' => null,
    ]);
    ComposerJson::factory()->create([
        'repository_id' => $repository->id,
        'type' => 'project',
    ]);

    $composerWarningEvaluator = $this->app->make(ComposerWarningEvaluator::class);
    $conditionObservations = $composerWarningEvaluator->conditionObservations($repository);

    $hasResolvedObservation = $conditionObservations->contains(static function (ConditionObservation $conditionObservation) use ($alertType, $now, $repository): bool {
        return $conditionObservation->alertType === $alertType
            && $conditionObservation->subject === $repository
            && $conditionObservation->condition === ConditionState::RESOLVED
            && $conditionObservation->observedAt->equalTo($now);
    });
    expect($hasResolvedObservation)
        ->toBeTrue();
})->with([
    AlertType::COMPOSER_LIBRARY_WITH_LOCK,
    AlertType::COMPOSER_TYPE_MISSING,
    AlertType::COMPOSER_TYPE_UNKNOWN,
]);

test('conditionObservations observes every composer warning resolved now for a repository without a composer warning', function (AlertType $alertType): void {
    $now = CarbonImmutable::parse(fake()->iso8601());
    $this->app->instance(ClockInterface::class, new MockClock($now));
    $repository = Repository::factory()->create(['composer_json_oid' => null]);

    $composerWarningEvaluator = $this->app->make(ComposerWarningEvaluator::class);
    $conditionObservations = $composerWarningEvaluator->conditionObservations($repository);

    $hasResolvedObservation = $conditionObservations->contains(static function (ConditionObservation $conditionObservation) use ($alertType, $now, $repository): bool {
        return $conditionObservation->alertType === $alertType
            && $conditionObservation->subject === $repository
            && $conditionObservation->condition === ConditionState::RESOLVED
            && $conditionObservation->observedAt->equalTo($now);
    });
    expect($hasResolvedObservation)
        ->toBeTrue();
})->with([
    AlertType::COMPOSER_LIBRARY_WITH_LOCK,
    AlertType::COMPOSER_TYPE_MISSING,
    AlertType::COMPOSER_PROJECT_WITHOUT_LOCK,
    AlertType::COMPOSER_TYPE_UNKNOWN,
]);
