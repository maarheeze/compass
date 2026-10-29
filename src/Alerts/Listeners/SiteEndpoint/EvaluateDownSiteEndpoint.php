<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts\Listeners\SiteEndpoint;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Alerts\ConditionState;
use Maarheeze\Compass\Alerts\Events\ConditionObserved;
use Maarheeze\Compass\Collector\Events\SiteEndpointChecked;

readonly class EvaluateDownSiteEndpoint implements ShouldQueue
{
    public function __construct(
        private Dispatcher $events,
    ) {
    }

    public function handle(SiteEndpointChecked $siteEndpointChecked): void
    {
        $siteEndpointCheck = $siteEndpointChecked->siteEndpointCheck;
        $observedCondition = match ($siteEndpointCheck->is_successful) {
            true => ConditionState::RESOLVED,
            false => ConditionState::ACTIVE,
        };

        $this->events->dispatch(new ConditionObserved(
            AlertType::DOWN_SITE_ENDPOINT,
            $siteEndpointChecked->siteEndpoint,
            $observedCondition,
            $siteEndpointCheck->created_at,
        ));
    }
}
