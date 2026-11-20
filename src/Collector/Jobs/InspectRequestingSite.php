<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Maarheeze\Compass\Collector\SiteReports\Jobs\InspectSite;
use Maarheeze\Compass\Models\Site;

class InspectRequestingSite implements ShouldQueue
{
    use InteractsWithQueue;
    use Queueable;

    public function __construct(
        public readonly string $key,
    ) {
    }

    public function handle(QueueingDispatcher $dispatcher): void
    {
        $requestingSite = Site::query()
            ->where('key', $this->key)
            ->first();

        if (!$requestingSite instanceof Site) {
            return;
        }

        foreach ($requestingSite->reports as $siteReport) {
            $dispatcher->dispatch(new InspectSite($requestingSite, $siteReport->source));
        }
    }
}
