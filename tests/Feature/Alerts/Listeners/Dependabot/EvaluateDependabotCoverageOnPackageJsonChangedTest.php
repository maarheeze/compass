<?php

declare(strict_types=1);

use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Support\Testing\Fakes\BusFake;
use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Alerts\ConditionObservation;
use Maarheeze\Compass\Alerts\Jobs\HandleConditionObservations;
use Maarheeze\Compass\Alerts\Listeners\Dependabot\EvaluateDependabotCoverageOnPackageJsonChanged;
use Maarheeze\Compass\Collector\BuiltInDriver;
use Maarheeze\Compass\Collector\Events\PackageJsonChanged;
use Maarheeze\Compass\Models\Repository;

test('handle observes the changed repository', function (): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(QueueingDispatcher::class, $busFake);
    $source = fake()->slug();
    $this->setConfiguration('compass.sources', [
        $source => [
            'driver' => BuiltInDriver::GITHUB,
        ],
    ]);
    $repository = Repository::factory()->create(['source' => $source]);

    $evaluateDependabotCoverageOnPackageJsonChanged = $this->app->make(EvaluateDependabotCoverageOnPackageJsonChanged::class);
    $evaluateDependabotCoverageOnPackageJsonChanged->handle(new PackageJsonChanged($repository));

    $busFake->assertDispatched(
        HandleConditionObservations::class,
        static function (HandleConditionObservations $handleConditionObservations) use ($repository): bool {
            return $handleConditionObservations->conditionObservations->contains(static function (ConditionObservation $conditionObservation) use ($repository): bool {
                return $conditionObservation->alertType === AlertType::DEPENDABOT_COMPOSER_MISSING
                    && $conditionObservation->subject instanceof Repository
                    && $conditionObservation->subject->is($repository);
            });
        },
    );
});
