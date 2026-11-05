<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Support\Testing\Fakes\BusFake;
use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Alerts\ConditionObservation;
use Maarheeze\Compass\Alerts\ConditionState;
use Maarheeze\Compass\Alerts\Jobs\HandleConditionObservations;
use Maarheeze\Compass\Alerts\Listeners\GitHub\EvaluateDependabotCoverage;
use Maarheeze\Compass\Collector\Events\ComposerJsonChanged;
use Maarheeze\Compass\Collector\Events\DependabotYmlChanged;
use Maarheeze\Compass\Collector\Events\PackageJsonChanged;
use Maarheeze\Compass\Models\Repository;
use Psr\Clock\ClockInterface;
use Symfony\Component\Clock\MockClock;

test('handle observes an uncovered package ecosystem active now', function (): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(QueueingDispatcher::class, $busFake);
    $now = CarbonImmutable::parse(fake()->iso8601());
    $this->app->instance(ClockInterface::class, new MockClock($now));
    $repository = Repository::factory()->create(['package_json_oid' => fake()->sha1()]);

    $evaluateDependabotCoverage = $this->app->make(EvaluateDependabotCoverage::class);
    $evaluateDependabotCoverage->handle(fake()->randomElement([
        new ComposerJsonChanged($repository),
        new DependabotYmlChanged($repository),
        new PackageJsonChanged($repository),
    ]));

    $busFake->assertDispatched(
        HandleConditionObservations::class,
        static function (HandleConditionObservations $handleConditionObservations) use ($now, $repository): bool {
            return $handleConditionObservations->conditionObservations->contains(static function (ConditionObservation $conditionObservation) use ($now, $repository): bool {
                return $conditionObservation->alertType === AlertType::DEPENDABOT_NPM_MISSING
                    && $conditionObservation->subject instanceof Repository
                    && $conditionObservation->subject->is($repository)
                    && $conditionObservation->condition === ConditionState::ACTIVE
                    && $conditionObservation->observedAt->equalTo($now);
            });
        },
    );
});

test('handle observes a package ecosystem the repository does not need resolved now', function (AlertType $alertType): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(QueueingDispatcher::class, $busFake);
    $now = CarbonImmutable::parse(fake()->iso8601());
    $this->app->instance(ClockInterface::class, new MockClock($now));
    $repository = Repository::factory()->create([
        'composer_json_oid' => null,
        'package_json_oid' => null,
    ]);

    $evaluateDependabotCoverage = $this->app->make(EvaluateDependabotCoverage::class);
    $evaluateDependabotCoverage->handle(fake()->randomElement([
        new ComposerJsonChanged($repository),
        new DependabotYmlChanged($repository),
        new PackageJsonChanged($repository),
    ]));

    $busFake->assertDispatched(
        HandleConditionObservations::class,
        static function (HandleConditionObservations $handleConditionObservations) use ($alertType, $now, $repository): bool {
            return $handleConditionObservations->conditionObservations->contains(static function (ConditionObservation $conditionObservation) use ($alertType, $now, $repository): bool {
                return $conditionObservation->alertType === $alertType
                    && $conditionObservation->subject instanceof Repository
                    && $conditionObservation->subject->is($repository)
                    && $conditionObservation->condition === ConditionState::RESOLVED
                    && $conditionObservation->observedAt->equalTo($now);
            });
        },
    );
})->with([
    AlertType::DEPENDABOT_COMPOSER_MISSING,
    AlertType::DEPENDABOT_NPM_MISSING,
]);
