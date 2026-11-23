<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Runs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Maarheeze\Compass\Collector\RunRecorder;
use Maarheeze\Compass\Models\Site;

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

        $runRecorder->startSiteInspections($requestingSite);
    }
}
