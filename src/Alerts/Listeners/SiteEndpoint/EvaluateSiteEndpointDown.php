<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts\Listeners\SiteEndpoint;

use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maarheeze\Compass\Alerts\ConditionObservation;
use Maarheeze\Compass\Alerts\ConditionState;
use Maarheeze\Compass\Alerts\Jobs\HandleConditionObservations;
use Maarheeze\Compass\Alerts\Types\SiteEndpointDown;
use Maarheeze\Compass\Collections\ConditionObservationCollection;
use Maarheeze\Compass\Collector\Events\SiteEndpointChecked;
use Maarheeze\Compass\Models\SiteEndpointCheck;

readonly class EvaluateSiteEndpointDown implements ShouldQueue
{
    public function __construct(
        private QueueingDispatcher $dispatcher,
    ) {
    }

    public function handle(SiteEndpointChecked $siteEndpointChecked): void
    {
        $siteEndpointCheck = $siteEndpointChecked->siteEndpointCheck;
        $conditionObservation = new ConditionObservation(
            new SiteEndpointDown(),
            $siteEndpointChecked->siteEndpoint,
            $this->observedCondition($siteEndpointCheck),
            $siteEndpointCheck->created_at,
        );
        $conditionObservations = new ConditionObservationCollection([
            $conditionObservation,
        ]);

        $this->dispatcher->dispatch(new HandleConditionObservations($conditionObservations));
    }

    private function observedCondition(SiteEndpointCheck $siteEndpointCheck): ConditionState
    {
        if ($siteEndpointCheck->is_successful) {
            return ConditionState::RESOLVED;
        }

        return ConditionState::ACTIVE;
    }
}
