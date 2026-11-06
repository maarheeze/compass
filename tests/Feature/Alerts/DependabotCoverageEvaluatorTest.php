<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Alerts\ConditionObservation;
use Maarheeze\Compass\Alerts\ConditionState;
use Maarheeze\Compass\Alerts\DependabotCoverageEvaluator;
use Maarheeze\Compass\Collector\BuiltInDriver;
use Maarheeze\Compass\Models\Repository;
use Psr\Clock\ClockInterface;
use Symfony\Component\Clock\MockClock;

test('conditionObservations observes an uncovered package ecosystem active now', function (): void {
    $now = CarbonImmutable::parse(fake()->iso8601());
    $this->app->instance(ClockInterface::class, new MockClock($now));
    $source = fake()->slug();
    $this->setConfiguration('compass.sources', [
        $source => [
            'driver' => BuiltInDriver::GITHUB,
        ],
    ]);
    $repository = Repository::factory()->create([
        'package_json_oid' => fake()->sha1(),
        'source' => $source,
    ]);

    $dependabotCoverageEvaluator = $this->app->make(DependabotCoverageEvaluator::class);
    $conditionObservations = $dependabotCoverageEvaluator->conditionObservations($repository);

    $hasActiveObservation = $conditionObservations->contains(static function (ConditionObservation $conditionObservation) use ($now, $repository): bool {
        return $conditionObservation->alertType === AlertType::DEPENDABOT_NPM_MISSING
            && $conditionObservation->subject === $repository
            && $conditionObservation->condition === ConditionState::ACTIVE
            && $conditionObservation->observedAt->equalTo($now);
    });
    expect($hasActiveObservation)
        ->toBeTrue();
});

test('conditionObservations observes a package ecosystem the repository does not need resolved now', function (AlertType $alertType): void {
    $now = CarbonImmutable::parse(fake()->iso8601());
    $this->app->instance(ClockInterface::class, new MockClock($now));
    $source = fake()->slug();
    $this->setConfiguration('compass.sources', [
        $source => [
            'driver' => BuiltInDriver::GITHUB,
        ],
    ]);
    $repository = Repository::factory()->create([
        'composer_json_oid' => null,
        'package_json_oid' => null,
        'source' => $source,
    ]);

    $dependabotCoverageEvaluator = $this->app->make(DependabotCoverageEvaluator::class);
    $conditionObservations = $dependabotCoverageEvaluator->conditionObservations($repository);

    $hasResolvedObservation = $conditionObservations->contains(static function (ConditionObservation $conditionObservation) use ($alertType, $now, $repository): bool {
        return $conditionObservation->alertType === $alertType
            && $conditionObservation->subject === $repository
            && $conditionObservation->condition === ConditionState::RESOLVED
            && $conditionObservation->observedAt->equalTo($now);
    });
    expect($hasResolvedObservation)
        ->toBeTrue();
})->with([
    AlertType::DEPENDABOT_COMPOSER_MISSING,
    AlertType::DEPENDABOT_NPM_MISSING,
]);
