<?php

declare(strict_types=1);

use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Support\Testing\Fakes\BusFake;
use Maarheeze\Compass\Alerts\ConditionObservation;
use Maarheeze\Compass\Alerts\Jobs\HandleConditionObservations;
use Maarheeze\Compass\Alerts\Listeners\Site\EvaluateSiteMissingOnSitesCollected;
use Maarheeze\Compass\Alerts\Types\SiteMissing;
use Maarheeze\Compass\Collector\Events\SitesCollected;
use Maarheeze\Compass\Models\Site;

test('handle observes a site of the source', function (): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(QueueingDispatcher::class, $busFake);
    $site = Site::factory()->create();

    $evaluateSiteMissingOnSitesCollected = $this->app->make(EvaluateSiteMissingOnSitesCollected::class);
    $evaluateSiteMissingOnSitesCollected->handle(new SitesCollected($site->source));

    $busFake->assertDispatched(
        HandleConditionObservations::class,
        static function (HandleConditionObservations $handleConditionObservations) use ($site): bool {
            return $handleConditionObservations->conditionObservations->contains(static function (ConditionObservation $conditionObservation) use ($site): bool {
                return $conditionObservation->alertType instanceof SiteMissing
                    && $conditionObservation->subject instanceof Site
                    && $conditionObservation->subject->is($site);
            });
        },
    );
});

test('handle observes nothing for a site of another source', function (): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(QueueingDispatcher::class, $busFake);
    Site::factory()->create(['source' => fake()->unique()->slug()]);

    $evaluateSiteMissingOnSitesCollected = $this->app->make(EvaluateSiteMissingOnSitesCollected::class);
    $evaluateSiteMissingOnSitesCollected->handle(new SitesCollected(fake()->unique()->slug()));

    $busFake->assertNothingDispatched();
});
