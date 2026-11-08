<?php

declare(strict_types=1);

use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Support\Testing\Fakes\BusFake;
use Maarheeze\Compass\Alerts\ConditionObservation;
use Maarheeze\Compass\Alerts\Jobs\HandleConditionObservations;
use Maarheeze\Compass\Alerts\Listeners\Repository\EvaluateRepositoryMissingOnRepositoryMissingIgnored;
use Maarheeze\Compass\Alerts\Types\RepositoryMissing;
use Maarheeze\Compass\Collector\Events\RepositoryMissingIgnored;
use Maarheeze\Compass\Models\Repository;

test('handle observes the ignored repository', function (): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(QueueingDispatcher::class, $busFake);
    $repository = Repository::factory()->create([
        'missing_at' => fake()->dateTimeBetween('-2 months', '-1 month'),
        'missing_ignored_at' => fake()->dateTimeBetween('-1 month'),
    ]);

    $evaluateRepositoryMissingOnRepositoryMissingIgnored = $this->app->make(EvaluateRepositoryMissingOnRepositoryMissingIgnored::class);
    $evaluateRepositoryMissingOnRepositoryMissingIgnored->handle(new RepositoryMissingIgnored($repository));

    $busFake->assertDispatched(
        HandleConditionObservations::class,
        static function (HandleConditionObservations $handleConditionObservations) use ($repository): bool {
            return $handleConditionObservations->conditionObservations->contains(static function (ConditionObservation $conditionObservation) use ($repository): bool {
                return $conditionObservation->alertType instanceof RepositoryMissing
                    && $conditionObservation->subject instanceof Repository
                    && $conditionObservation->subject->is($repository);
            });
        },
    );
});
