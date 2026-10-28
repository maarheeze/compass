<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Console;

use Illuminate\Console\Command;
use Maarheeze\Compass\Collector\Events\EndpointCheckRunSucceeded;
use Maarheeze\Compass\Collector\RunRecorder;
use Maarheeze\Compass\Collector\RunType;
use Maarheeze\Compass\Collector\SiteEndpoint\Jobs\CheckSiteEndpoints;
use Maarheeze\Uuid\Uuid;

class CheckSiteEndpointsCommand extends Command
{
    public const string SIGNATURE = 'compass:endpoints-check';

    public function handle(RunRecorder $runRecorder): void
    {
        $runId = Uuid::generate();

        $runRecorder->start(
            $runId,
            RunType::ENDPOINT_CHECK,
            new CheckSiteEndpoints(),
            new EndpointCheckRunSucceeded($runId),
        );
    }

    protected function configure(): void
    {
        $this
            ->setName(self::SIGNATURE)
            ->setDescription('Check the endpoints the sites report and store the results');
    }
}
