<?php

declare(strict_types=1);

use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Support\Testing\Fakes\BusFake;
use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Alerts\ConditionObservation;
use Maarheeze\Compass\Alerts\Jobs\HandleConditionObservations;
use Maarheeze\Compass\Alerts\Listeners\Site\EvaluateSiteMissingOnSiteMissingIgnored;
use Maarheeze\Compass\Collector\Events\SiteMissingIgnored;
use Maarheeze\Compass\Models\Site;

test('handle observes the ignored site', function (): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(QueueingDispatcher::class, $busFake);
    $site = Site::factory()->create([
        'missing_at' => fake()->dateTimeBetween('-2 months', '-1 month'),
        'missing_ignored_at' => fake()->dateTimeBetween('-1 month'),
    ]);

    $evaluateSiteMissingOnSiteMissingIgnored = $this->app->make(EvaluateSiteMissingOnSiteMissingIgnored::class);
    $evaluateSiteMissingOnSiteMissingIgnored->handle(new SiteMissingIgnored($site));

    $busFake->assertDispatched(
        HandleConditionObservations::class,
        static function (HandleConditionObservations $handleConditionObservations) use ($site): bool {
            return $handleConditionObservations->conditionObservations->contains(static function (ConditionObservation $conditionObservation) use ($site): bool {
                return $conditionObservation->alertType === AlertType::SITE_MISSING
                    && $conditionObservation->subject instanceof Site
                    && $conditionObservation->subject->is($site);
            });
        },
    );
});
