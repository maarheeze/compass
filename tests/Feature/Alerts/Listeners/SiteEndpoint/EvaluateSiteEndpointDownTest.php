<?php

declare(strict_types=1);

use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Http\Response;
use Illuminate\Support\Testing\Fakes\BusFake;
use Maarheeze\Compass\Alerts\ConditionObservation;
use Maarheeze\Compass\Alerts\ConditionState;
use Maarheeze\Compass\Alerts\Jobs\HandleConditionObservations;
use Maarheeze\Compass\Alerts\Listeners\SiteEndpoint\EvaluateSiteEndpointDown;
use Maarheeze\Compass\Alerts\Types\SiteEndpointDown;
use Maarheeze\Compass\Collector\Events\SiteEndpointChecked;
use Maarheeze\Compass\Collector\SiteEndpoint\SiteEndpointState;
use Maarheeze\Compass\Models\SiteEndpoint;
use Maarheeze\Compass\Models\SiteEndpointCheck;
use Maarheeze\Compass\Models\SiteReport;

test('handle observes the endpoint down at the check for a failed check', function (): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(QueueingDispatcher::class, $busFake);
    $siteEndpoint = SiteEndpoint::factory()
        ->for(SiteReport::factory()->state(['maintenance_mode' => false]))
        ->create();
    $siteEndpointCheck = SiteEndpointCheck::factory()->create(['state' => SiteEndpointState::DOWN]);

    $evaluateDownSiteEndpoint = $this->app->make(EvaluateSiteEndpointDown::class);
    $evaluateDownSiteEndpoint->handle(new SiteEndpointChecked($siteEndpoint, $siteEndpointCheck));

    $busFake->assertDispatched(
        HandleConditionObservations::class,
        static function (HandleConditionObservations $handleConditionObservations) use ($siteEndpoint, $siteEndpointCheck): bool {
            return $handleConditionObservations->conditionObservations->contains(static function (ConditionObservation $conditionObservation) use ($siteEndpoint, $siteEndpointCheck): bool {
                return $conditionObservation->alertType instanceof SiteEndpointDown
                    && $conditionObservation->subject === $siteEndpoint
                    && $conditionObservation->condition === ConditionState::ACTIVE
                    && $conditionObservation->observedAt->equalTo($siteEndpointCheck->created_at);
            });
        },
    );
});

test('handle observes the endpoint no longer down at the check for a service unavailable check during maintenance mode', function (): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(QueueingDispatcher::class, $busFake);
    $siteEndpoint = SiteEndpoint::factory()
        ->for(SiteReport::factory()->state(['maintenance_mode' => true]))
        ->create();
    $siteEndpointCheck = SiteEndpointCheck::factory()->create([
        'state' => SiteEndpointState::DOWN,
        'status_code' => Response::HTTP_SERVICE_UNAVAILABLE,
    ]);

    $evaluateDownSiteEndpoint = $this->app->make(EvaluateSiteEndpointDown::class);
    $evaluateDownSiteEndpoint->handle(new SiteEndpointChecked($siteEndpoint, $siteEndpointCheck));

    $busFake->assertDispatched(
        HandleConditionObservations::class,
        static function (HandleConditionObservations $handleConditionObservations) use ($siteEndpoint, $siteEndpointCheck): bool {
            return $handleConditionObservations->conditionObservations->contains(static function (ConditionObservation $conditionObservation) use ($siteEndpoint, $siteEndpointCheck): bool {
                return $conditionObservation->alertType instanceof SiteEndpointDown
                    && $conditionObservation->subject === $siteEndpoint
                    && $conditionObservation->condition === ConditionState::RESOLVED
                    && $conditionObservation->observedAt->equalTo($siteEndpointCheck->created_at);
            });
        },
    );
});

test('handle observes the endpoint down at the check for another failed check during maintenance mode', function (): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(QueueingDispatcher::class, $busFake);
    $siteEndpoint = SiteEndpoint::factory()
        ->for(SiteReport::factory()->state(['maintenance_mode' => true]))
        ->create();
    $siteEndpointCheck = SiteEndpointCheck::factory()->create([
        'state' => SiteEndpointState::DOWN,
        'status_code' => Response::HTTP_INTERNAL_SERVER_ERROR,
    ]);

    $evaluateDownSiteEndpoint = $this->app->make(EvaluateSiteEndpointDown::class);
    $evaluateDownSiteEndpoint->handle(new SiteEndpointChecked($siteEndpoint, $siteEndpointCheck));

    $busFake->assertDispatched(
        HandleConditionObservations::class,
        static function (HandleConditionObservations $handleConditionObservations) use ($siteEndpoint, $siteEndpointCheck): bool {
            return $handleConditionObservations->conditionObservations->contains(static function (ConditionObservation $conditionObservation) use ($siteEndpoint, $siteEndpointCheck): bool {
                return $conditionObservation->alertType instanceof SiteEndpointDown
                    && $conditionObservation->subject === $siteEndpoint
                    && $conditionObservation->condition === ConditionState::ACTIVE
                    && $conditionObservation->observedAt->equalTo($siteEndpointCheck->created_at);
            });
        },
    );
});

test('handle observes the endpoint no longer down at the check for an up check', function (): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(QueueingDispatcher::class, $busFake);
    $siteEndpoint = SiteEndpoint::factory()->create();
    $siteEndpointCheck = SiteEndpointCheck::factory()->create(['state' => SiteEndpointState::UP]);

    $evaluateDownSiteEndpoint = $this->app->make(EvaluateSiteEndpointDown::class);
    $evaluateDownSiteEndpoint->handle(new SiteEndpointChecked($siteEndpoint, $siteEndpointCheck));

    $busFake->assertDispatched(
        HandleConditionObservations::class,
        static function (HandleConditionObservations $handleConditionObservations) use ($siteEndpoint, $siteEndpointCheck): bool {
            return $handleConditionObservations->conditionObservations->contains(static function (ConditionObservation $conditionObservation) use ($siteEndpoint, $siteEndpointCheck): bool {
                return $conditionObservation->alertType instanceof SiteEndpointDown
                    && $conditionObservation->subject === $siteEndpoint
                    && $conditionObservation->condition === ConditionState::RESOLVED
                    && $conditionObservation->observedAt->equalTo($siteEndpointCheck->created_at);
            });
        },
    );
});
