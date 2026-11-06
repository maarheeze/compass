<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts\Listeners\Site;

use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maarheeze\Compass\Alerts\Jobs\HandleConditionObservations;
use Maarheeze\Compass\Alerts\SiteMissingEvaluator;
use Maarheeze\Compass\Collections\ConditionObservationCollection;
use Maarheeze\Compass\Collector\Events\SitesCollected;
use Maarheeze\Compass\Models\Site;

readonly class EvaluateSiteMissingOnSitesCollected implements ShouldQueue
{
    public function __construct(
        private QueueingDispatcher $dispatcher,
        private SiteMissingEvaluator $siteMissingEvaluator,
    ) {
    }

    public function handle(SitesCollected $sitesCollected): void
    {
        $siteChunks = Site::query()
            ->ofSource($sitesCollected->source)
            ->lazyById()
            ->chunk(100);

        foreach ($siteChunks as $sites) {
            $this->dispatcher->dispatch(new HandleConditionObservations(new ConditionObservationCollection(
                $sites->map($this->siteMissingEvaluator->conditionObservation(...)),
            )));
        }
    }
}
