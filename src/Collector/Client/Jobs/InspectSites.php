<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Client\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maarheeze\Compass\Models\Site;

class InspectSites implements ShouldQueue
{
    use Queueable;

    public function handle(Dispatcher $dispatcher): void
    {
        $collectedSites = Site::query()
            ->whereNull('missing_at')
            ->get();

        foreach ($collectedSites as $collectedSite) {
            $dispatcher->dispatch(new InspectSite($collectedSite));
        }
    }
}
