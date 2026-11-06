<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts\Listeners\Site;

use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maarheeze\Compass\Alerts\Jobs\HandleConditionObservations;
use Maarheeze\Compass\Alerts\SiteBehindEvaluator;
use Maarheeze\Compass\Collections\ConditionObservationCollection;
use Maarheeze\Compass\Collector\Events\SitesCollected;
use Maarheeze\Compass\Models\Site;

readonly class EvaluateSiteBehindOnSitesCollected implements ShouldQueue
{
    public function __construct(
        private QueueingDispatcher $dispatcher,
        private SiteBehindEvaluator $siteBehindEvaluator,
    ) {
    }

    public function handle(SitesCollected $sitesCollected): void
    {
        $siteChunks = Site::query()
            ->ofSource($sitesCollected->source)
            ->with('repository')
            ->lazyById()
            ->chunk(100);

        foreach ($siteChunks as $sites) {
            $this->dispatcher->dispatch(new HandleConditionObservations(new ConditionObservationCollection(
                $sites->map($this->siteBehindEvaluator->conditionObservation(...)),
            )));
        }
    }
}
