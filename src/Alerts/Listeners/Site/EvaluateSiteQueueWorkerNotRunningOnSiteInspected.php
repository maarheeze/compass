<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts\Listeners\Site;

use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maarheeze\Compass\Alerts\Jobs\HandleConditionObservations;
use Maarheeze\Compass\Alerts\SiteQueueWorkerNotRunningEvaluator;
use Maarheeze\Compass\Collections\ConditionObservationCollection;
use Maarheeze\Compass\Collector\Events\SiteInspected;

readonly class EvaluateSiteQueueWorkerNotRunningOnSiteInspected implements ShouldQueue
{
    public function __construct(
        private QueueingDispatcher $dispatcher,
        private SiteQueueWorkerNotRunningEvaluator $siteQueueWorkerNotRunningEvaluator,
    ) {
    }

    public function handle(SiteInspected $siteInspected): void
    {
        $site = $siteInspected->siteReport->site;
        $conditionObservation = $this->siteQueueWorkerNotRunningEvaluator->conditionObservation($site);

        if ($conditionObservation === null) {
            return;
        }

        $this->dispatcher->dispatch(new HandleConditionObservations(new ConditionObservationCollection([
            $conditionObservation,
        ])));
    }
}
