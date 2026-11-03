<?php

declare(strict_types=1);

use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Support\Testing\Fakes\BusFake;
use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Alerts\ConditionState;
use Maarheeze\Compass\Alerts\Events\ConditionObserved;
use Maarheeze\Compass\Alerts\Jobs\HandleConditionObservations;
use Maarheeze\Compass\Alerts\Listeners\SiteEndpoint\EvaluateSiteEndpointDown;
use Maarheeze\Compass\Collector\Events\SiteEndpointChecked;
use Maarheeze\Compass\Models\SiteEndpoint;
use Maarheeze\Compass\Models\SiteEndpointCheck;

test('handle observes the endpoint down at the check for a failed check', function (): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(QueueingDispatcher::class, $busFake);
    $siteEndpoint = SiteEndpoint::factory()->create();
    $siteEndpointCheck = SiteEndpointCheck::factory()->create(['is_successful' => false]);

    $evaluateDownSiteEndpoint = $this->app->make(EvaluateSiteEndpointDown::class);
    $evaluateDownSiteEndpoint->handle(new SiteEndpointChecked($siteEndpoint, $siteEndpointCheck));

    $busFake->assertDispatched(
        HandleConditionObservations::class,
        static function (HandleConditionObservations $handleConditionObservations) use ($siteEndpoint, $siteEndpointCheck): bool {
            return $handleConditionObservations->conditionObservations->contains(static function (ConditionObserved $conditionObserved) use ($siteEndpoint, $siteEndpointCheck): bool {
                return $conditionObserved->alertType === AlertType::SITE_ENDPOINT_DOWN
                    && $conditionObserved->subject === $siteEndpoint
                    && $conditionObserved->condition === ConditionState::ACTIVE
                    && $conditionObserved->observedAt->equalTo($siteEndpointCheck->created_at);
            });
        },
    );
});

test('handle observes the endpoint no longer down at the check for a successful check', function (): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(QueueingDispatcher::class, $busFake);
    $siteEndpoint = SiteEndpoint::factory()->create();
    $siteEndpointCheck = SiteEndpointCheck::factory()->create(['is_successful' => true]);

    $evaluateDownSiteEndpoint = $this->app->make(EvaluateSiteEndpointDown::class);
    $evaluateDownSiteEndpoint->handle(new SiteEndpointChecked($siteEndpoint, $siteEndpointCheck));

    $busFake->assertDispatched(
        HandleConditionObservations::class,
        static function (HandleConditionObservations $handleConditionObservations) use ($siteEndpoint, $siteEndpointCheck): bool {
            return $handleConditionObservations->conditionObservations->contains(static function (ConditionObserved $conditionObserved) use ($siteEndpoint, $siteEndpointCheck): bool {
                return $conditionObserved->alertType === AlertType::SITE_ENDPOINT_DOWN
                    && $conditionObserved->subject === $siteEndpoint
                    && $conditionObserved->condition === ConditionState::RESOLVED
                    && $conditionObserved->observedAt->equalTo($siteEndpointCheck->created_at);
            });
        },
    );
});
