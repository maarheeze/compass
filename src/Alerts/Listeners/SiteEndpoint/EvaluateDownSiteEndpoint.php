<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts\Listeners\SiteEndpoint;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Alerts\ConditionState;
use Maarheeze\Compass\Alerts\Events\ConditionObserved;
use Maarheeze\Compass\Collector\Events\SiteEndpointChecked;
use Maarheeze\Compass\Models\SiteEndpointCheck;

readonly class EvaluateDownSiteEndpoint implements ShouldQueue
{
    public function __construct(
        private Dispatcher $events,
    ) {
    }

    public function handle(SiteEndpointChecked $siteEndpointChecked): void
    {
        $siteEndpointCheck = $siteEndpointChecked->siteEndpointCheck;
        $this->events->dispatch(new ConditionObserved(
            AlertType::DOWN_SITE_ENDPOINT,
            $siteEndpointChecked->siteEndpoint,
            $this->observedCondition($siteEndpointCheck),
            $siteEndpointCheck->created_at,
        ));
    }

    private function observedCondition(SiteEndpointCheck $siteEndpointCheck): ConditionState
    {
        if ($siteEndpointCheck->is_successful) {
            return ConditionState::RESOLVED;
        }

        return ConditionState::ACTIVE;
    }
}
