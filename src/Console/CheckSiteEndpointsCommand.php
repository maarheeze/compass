<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Console;

use Illuminate\Console\Command;
use Maarheeze\Compass\Collector\RunRecorder;
use Maarheeze\Compass\Collector\SiteEndpoint\Jobs\CheckSiteEndpoints;
use Maarheeze\Compass\Collector\TaskType;
use Maarheeze\Uuid\Uuid;

class CheckSiteEndpointsCommand extends Command
{
    public const string SIGNATURE = 'compass:endpoints-check';

    public function handle(RunRecorder $runRecorder): void
    {
        $runRecorder->startTask(Uuid::generate(), TaskType::SITE_ENDPOINTS, new CheckSiteEndpoints());
    }

    protected function configure(): void
    {
        $this
            ->setName(self::SIGNATURE)
            ->setDescription('Check the endpoints the sites report and store the results');
    }
}
