<?php

declare(strict_types=1);

use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Support\Testing\Fakes\BusFake;
use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Alerts\ConditionObservation;
use Maarheeze\Compass\Alerts\Jobs\HandleConditionObservations;
use Maarheeze\Compass\Alerts\Listeners\Forge\EvaluateSiteBehindOnSitesCollected;
use Maarheeze\Compass\Collector\Events\SitesCollected;
use Maarheeze\Compass\Models\Site;

test('handle observes a site of the source', function (): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(QueueingDispatcher::class, $busFake);
    $site = Site::factory()->create();

    $evaluateSiteBehindOnSitesCollected = $this->app->make(EvaluateSiteBehindOnSitesCollected::class);
    $evaluateSiteBehindOnSitesCollected->handle(new SitesCollected($site->source));

    $busFake->assertDispatched(
        HandleConditionObservations::class,
        static function (HandleConditionObservations $handleConditionObservations) use ($site): bool {
            return $handleConditionObservations->conditionObservations->contains(static function (ConditionObservation $conditionObservation) use ($site): bool {
                return $conditionObservation->alertType === AlertType::SITE_BEHIND
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

    $evaluateSiteBehindOnSitesCollected = $this->app->make(EvaluateSiteBehindOnSitesCollected::class);
    $evaluateSiteBehindOnSitesCollected->handle(new SitesCollected(fake()->unique()->slug()));

    $busFake->assertNothingDispatched();
});
