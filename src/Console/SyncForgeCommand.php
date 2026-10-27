<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Console;

use Illuminate\Console\Command;
use Illuminate\Contracts\Bus\Dispatcher;
use Maarheeze\Compass\Collector\Client\Jobs\InspectSites;
use Maarheeze\Compass\Collector\Forge\Jobs\CollectSites;
use Maarheeze\Compass\Collector\RunId;

class SyncForgeCommand extends Command
{
    public const string SIGNATURE = 'compass:forge-sync';

    public function handle(Dispatcher $dispatcher): void
    {
        $collectSites = new CollectSites(RunId::generate(), null);
        $collectSites->chain([
            new InspectSites(),
        ]);
        $dispatcher->dispatch($collectSites);
    }

    protected function configure(): void
    {
        $this
            ->setName(self::SIGNATURE)
            ->setDescription('Collect the sites from Forge, inspect them and synchronize the database');
    }
}
