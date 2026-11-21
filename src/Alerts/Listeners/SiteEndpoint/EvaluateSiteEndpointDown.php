<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts\Listeners\SiteEndpoint;

use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\Response;
use Maarheeze\Compass\Alerts\ConditionObservation;
use Maarheeze\Compass\Alerts\ConditionState;
use Maarheeze\Compass\Alerts\Jobs\HandleConditionObservations;
use Maarheeze\Compass\Alerts\Types\SiteEndpointDown;
use Maarheeze\Compass\Collections\ConditionObservationCollection;
use Maarheeze\Compass\Collector\Events\SiteEndpointChecked;
use Maarheeze\Compass\Collector\SiteEndpoint\SiteEndpointState;
use Maarheeze\Compass\Models\SiteEndpoint;
use Maarheeze\Compass\Models\SiteEndpointCheck;

readonly class EvaluateSiteEndpointDown implements ShouldQueue
{
    public function __construct(
        private QueueingDispatcher $dispatcher,
    ) {
    }

    public function handle(SiteEndpointChecked $siteEndpointChecked): void
    {
        $siteEndpoint = $siteEndpointChecked->siteEndpoint;
        $siteEndpointCheck = $siteEndpointChecked->siteEndpointCheck;
        $conditionObservation = new ConditionObservation(
            new SiteEndpointDown(),
            $siteEndpoint,
            $this->observedCondition($siteEndpoint, $siteEndpointCheck),
            $siteEndpointCheck->created_at,
        );
        $conditionObservations = new ConditionObservationCollection([
            $conditionObservation,
        ]);

        $this->dispatcher->dispatch(new HandleConditionObservations($conditionObservations));
    }

    private function observedCondition(SiteEndpoint $siteEndpoint, SiteEndpointCheck $siteEndpointCheck): ConditionState
    {
        if (
            $siteEndpoint->siteReport->maintenance_mode === true
            && $siteEndpointCheck->status_code === Response::HTTP_SERVICE_UNAVAILABLE
        ) {
            return ConditionState::RESOLVED;
        }

        return match ($siteEndpointCheck->state) {
            SiteEndpointState::DOWN => ConditionState::ACTIVE,
            SiteEndpointState::UP => ConditionState::RESOLVED,
        };
    }
}
