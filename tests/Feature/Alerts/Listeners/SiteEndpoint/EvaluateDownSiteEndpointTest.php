<?php

declare(strict_types=1);

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Testing\Fakes\EventFake;
use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Alerts\ConditionState;
use Maarheeze\Compass\Alerts\Events\ConditionObserved;
use Maarheeze\Compass\Alerts\Listeners\SiteEndpoint\EvaluateDownSiteEndpoint;
use Maarheeze\Compass\Collector\Events\SiteEndpointChecked;
use Maarheeze\Compass\Models\SiteEndpoint;
use Maarheeze\Compass\Models\SiteEndpointCheck;

test('handle observes the endpoint down at the check for a failed check', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));
    $this->app->instance(Dispatcher::class, $eventFake);
    $siteEndpoint = SiteEndpoint::factory()->create();
    $siteEndpointCheck = SiteEndpointCheck::factory()->create(['is_successful' => false]);

    $evaluateDownSiteEndpoint = $this->app->make(EvaluateDownSiteEndpoint::class);
    $evaluateDownSiteEndpoint->handle(new SiteEndpointChecked($siteEndpoint, $siteEndpointCheck));

    $eventFake->assertDispatched(ConditionObserved::class, static function (ConditionObserved $conditionObserved) use ($siteEndpoint, $siteEndpointCheck): bool {
        return $conditionObserved->alertType === AlertType::DOWN_SITE_ENDPOINT
            && $conditionObserved->subject === $siteEndpoint
            && $conditionObserved->condition === ConditionState::ACTIVE
            && $conditionObserved->observedAt->equalTo($siteEndpointCheck->created_at);
    });
});

test('handle observes the endpoint no longer down at the check for a successful check', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));
    $this->app->instance(Dispatcher::class, $eventFake);
    $siteEndpoint = SiteEndpoint::factory()->create();
    $siteEndpointCheck = SiteEndpointCheck::factory()->create(['is_successful' => true]);

    $evaluateDownSiteEndpoint = $this->app->make(EvaluateDownSiteEndpoint::class);
    $evaluateDownSiteEndpoint->handle(new SiteEndpointChecked($siteEndpoint, $siteEndpointCheck));

    $eventFake->assertDispatched(ConditionObserved::class, static function (ConditionObserved $conditionObserved) use ($siteEndpoint, $siteEndpointCheck): bool {
        return $conditionObserved->alertType === AlertType::DOWN_SITE_ENDPOINT
            && $conditionObserved->subject === $siteEndpoint
            && $conditionObserved->condition === ConditionState::RESOLVED
            && $conditionObserved->observedAt->equalTo($siteEndpointCheck->created_at);
    });
});
