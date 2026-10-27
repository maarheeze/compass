<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Console;

use Illuminate\Console\Command;
use Maarheeze\Compass\Collector\RunId;
use Maarheeze\Compass\Collector\RunRecorder;
use Maarheeze\Compass\Collector\RunType;
use Maarheeze\Compass\Collector\SiteEndpoint\Jobs\CheckSiteEndpoints;

class CheckSiteEndpointsCommand extends Command
{
    public const string SIGNATURE = 'compass:endpoints-check';

    public function handle(RunRecorder $runRecorder): void
    {
        $runRecorder->start(RunId::generate(), RunType::ENDPOINT_CHECK, new CheckSiteEndpoints());
    }

    protected function configure(): void
    {
        $this
            ->setName(self::SIGNATURE)
            ->setDescription('Check the endpoints the sites report and store the results');
    }
}
