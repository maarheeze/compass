<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Maarheeze\Compass\Collector\RunRecorder;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Uuid\Uuid;

class InspectRequestingSite implements ShouldQueue
{
    use InteractsWithQueue;
    use Queueable;

    public function __construct(
        public readonly string $key,
    ) {
    }

    public function handle(RunRecorder $runRecorder): void
    {
        $requestingSite = Site::query()
            ->where('key', $this->key)
            ->first();

        if (!$requestingSite instanceof Site) {
            return;
        }

        foreach ($requestingSite->reports as $siteReport) {
            $runRecorder->startSiteInspection(Uuid::generate(), $requestingSite, $siteReport->source);
        }
    }
}
