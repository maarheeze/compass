<?php

declare(strict_types=1);

use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Support\Testing\Fakes\BusFake;
use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Alerts\ConditionObservation;
use Maarheeze\Compass\Alerts\Jobs\HandleConditionObservations;
use Maarheeze\Compass\Alerts\Listeners\GitHub\EvaluateRepositoryMissingOnRepositoriesCollected;
use Maarheeze\Compass\Collector\Events\RepositoriesCollected;
use Maarheeze\Compass\Models\Repository;

test('handle observes a repository of the source', function (): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(QueueingDispatcher::class, $busFake);
    $repository = Repository::factory()->create();

    $evaluateRepositoryMissingOnRepositoriesCollected = $this->app->make(EvaluateRepositoryMissingOnRepositoriesCollected::class);
    $evaluateRepositoryMissingOnRepositoriesCollected->handle(new RepositoriesCollected($repository->source));

    $busFake->assertDispatched(
        HandleConditionObservations::class,
        static function (HandleConditionObservations $handleConditionObservations) use ($repository): bool {
            return $handleConditionObservations->conditionObservations->contains(static function (ConditionObservation $conditionObservation) use ($repository): bool {
                return $conditionObservation->alertType === AlertType::REPOSITORY_MISSING
                    && $conditionObservation->subject instanceof Repository
                    && $conditionObservation->subject->is($repository);
            });
        },
    );
});

test('handle observes nothing for a repository of another source', function (): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(QueueingDispatcher::class, $busFake);
    Repository::factory()->create(['source' => fake()->unique()->slug()]);

    $evaluateRepositoryMissingOnRepositoriesCollected = $this->app->make(EvaluateRepositoryMissingOnRepositoriesCollected::class);
    $evaluateRepositoryMissingOnRepositoriesCollected->handle(new RepositoriesCollected(fake()->unique()->slug()));

    $busFake->assertNothingDispatched();
});
