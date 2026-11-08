<?php

declare(strict_types=1);

use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Support\Testing\Fakes\BusFake;
use Maarheeze\Compass\Alerts\ConditionObservation;
use Maarheeze\Compass\Alerts\Jobs\HandleConditionObservations;
use Maarheeze\Compass\Alerts\Listeners\Site\EvaluateSiteBehindOnRepositoriesCollected;
use Maarheeze\Compass\Alerts\Types\SiteBehind;
use Maarheeze\Compass\Collector\Events\RepositoriesCollected;
use Maarheeze\Compass\Models\Repository;
use Maarheeze\Compass\Models\Site;

test('handle observes a site whose repository belongs to the source', function (): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(QueueingDispatcher::class, $busFake);
    $repository = Repository::factory()->create();
    $site = Site::factory()->create(['repository_url' => $repository->url]);

    $evaluateSiteBehindOnRepositoriesCollected = $this->app->make(EvaluateSiteBehindOnRepositoriesCollected::class);
    $evaluateSiteBehindOnRepositoriesCollected->handle(new RepositoriesCollected($repository->source));

    $busFake->assertDispatched(
        HandleConditionObservations::class,
        static function (HandleConditionObservations $handleConditionObservations) use ($site): bool {
            return $handleConditionObservations->conditionObservations->contains(static function (ConditionObservation $conditionObservation) use ($site): bool {
                return $conditionObservation->alertType instanceof SiteBehind
                    && $conditionObservation->subject instanceof Site
                    && $conditionObservation->subject->is($site);
            });
        },
    );
});

test('handle observes nothing for a site whose repository belongs to another source', function (): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(QueueingDispatcher::class, $busFake);
    $repository = Repository::factory()->create(['source' => fake()->unique()->slug()]);
    Site::factory()->create(['repository_url' => $repository->url]);

    $evaluateSiteBehindOnRepositoriesCollected = $this->app->make(EvaluateSiteBehindOnRepositoriesCollected::class);
    $evaluateSiteBehindOnRepositoriesCollected->handle(new RepositoriesCollected(fake()->unique()->slug()));

    $busFake->assertNothingDispatched();
});
