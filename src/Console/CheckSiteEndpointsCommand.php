<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Console;

use Illuminate\Console\Command;
use Illuminate\Contracts\Bus\Dispatcher;
use Maarheeze\Compass\Collector\SiteEndpoint\Jobs\CheckSiteEndpoints;

class CheckSiteEndpointsCommand extends Command
{
    public const string SIGNATURE = 'compass:endpoints-check';

    public function handle(Dispatcher $dispatcher): void
    {
        $dispatcher->dispatch(new CheckSiteEndpoints());
    }

    protected function configure(): void
    {
        $this
            ->setName(self::SIGNATURE)
            ->setDescription('Check the endpoints the sites report and store the results');
    }
}
