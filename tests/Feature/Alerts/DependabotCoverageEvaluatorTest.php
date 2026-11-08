<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Maarheeze\Compass\Alerts\ConditionObservation;
use Maarheeze\Compass\Alerts\ConditionState;
use Maarheeze\Compass\Alerts\DependabotCoverageEvaluator;
use Maarheeze\Compass\Alerts\Types\DependabotComposerMissing;
use Maarheeze\Compass\Alerts\Types\DependabotNpmMissing;
use Maarheeze\Compass\Models\DependabotYml;
use Maarheeze\Compass\Models\Repository;
use Psr\Clock\ClockInterface;
use Symfony\Component\Clock\MockClock;

test('conditionObservations observes an uncovered package ecosystem active now', function (): void {
    $now = CarbonImmutable::parse(fake()->iso8601());
    $this->app->instance(ClockInterface::class, new MockClock($now));
    $source = fake()->slug();
    $this->setConfiguration('compass.sources', [
        $source => [
            'driver' => 'github',
        ],
    ]);
    $repository = Repository::factory()->create([
        'package_json_version' => fake()->sha1(),
        'source' => $source,
    ]);

    $dependabotCoverageEvaluator = $this->app->make(DependabotCoverageEvaluator::class);
    $conditionObservations = $dependabotCoverageEvaluator->conditionObservations($repository);

    $hasActiveObservation = $conditionObservations->contains(static function (ConditionObservation $conditionObservation) use ($now, $repository): bool {
        return $conditionObservation->alertType instanceof DependabotNpmMissing
            && $conditionObservation->subject === $repository
            && $conditionObservation->condition === ConditionState::ACTIVE
            && $conditionObservation->observedAt->equalTo($now);
    });
    expect($hasActiveObservation)
        ->toBeTrue();
});

test('conditionObservations observes a package ecosystem the repository does not need resolved now', function (string $alertTypeClass): void {
    $now = CarbonImmutable::parse(fake()->iso8601());
    $this->app->instance(ClockInterface::class, new MockClock($now));
    $source = fake()->slug();
    $this->setConfiguration('compass.sources', [
        $source => [
            'driver' => 'github',
        ],
    ]);
    $repository = Repository::factory()->create([
        'composer_json_version' => null,
        'package_json_version' => null,
        'source' => $source,
    ]);

    $dependabotCoverageEvaluator = $this->app->make(DependabotCoverageEvaluator::class);
    $conditionObservations = $dependabotCoverageEvaluator->conditionObservations($repository);

    $hasResolvedObservation = $conditionObservations->contains(static function (ConditionObservation $conditionObservation) use ($alertTypeClass, $now, $repository): bool {
        return $conditionObservation->alertType instanceof $alertTypeClass
            && $conditionObservation->subject === $repository
            && $conditionObservation->condition === ConditionState::RESOLVED
            && $conditionObservation->observedAt->equalTo($now);
    });
    expect($hasResolvedObservation)
        ->toBeTrue();
})->with([
    DependabotComposerMissing::class,
    DependabotNpmMissing::class,
]);

test('conditionObservations judges the dependabot yml stored now, not one loaded before', function (): void {
    $source = fake()->slug();
    $this->setConfiguration('compass.sources', [
        $source => [
            'driver' => 'github',
        ],
    ]);
    $repository = Repository::factory()->create([
        'package_json_version' => fake()->sha1(),
        'source' => $source,
    ]);
    $repository->load('dependabotYml');
    DependabotYml::factory()->create([
        'repository_id' => $repository->id,
        'package_ecosystems' => ['npm'],
    ]);

    $dependabotCoverageEvaluator = $this->app->make(DependabotCoverageEvaluator::class);
    $conditionObservations = $dependabotCoverageEvaluator->conditionObservations($repository);

    $hasResolvedObservation = $conditionObservations->contains(static function (ConditionObservation $conditionObservation): bool {
        return $conditionObservation->alertType instanceof DependabotNpmMissing
            && $conditionObservation->condition === ConditionState::RESOLVED;
    });
    expect($hasResolvedObservation)
        ->toBeTrue();
});
