<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts\Listeners\Forge;

use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maarheeze\Compass\Alerts\Jobs\HandleConditionObservations;
use Maarheeze\Compass\Alerts\SiteMissingEvaluator;
use Maarheeze\Compass\Collections\ConditionObservationCollection;
use Maarheeze\Compass\Collector\Events\SiteMissingIgnored;

readonly class EvaluateSiteMissingOnSiteMissingIgnored implements ShouldQueue
{
    public function __construct(
        private QueueingDispatcher $dispatcher,
        private SiteMissingEvaluator $siteMissingEvaluator,
    ) {
    }

    public function handle(SiteMissingIgnored $siteMissingIgnored): void
    {
        $this->dispatcher->dispatch(new HandleConditionObservations(new ConditionObservationCollection([
            $this->siteMissingEvaluator->conditionObservation($siteMissingIgnored->site),
        ])));
    }
}
