<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Console;

use Illuminate\Console\Command;
use Illuminate\Contracts\Bus\Dispatcher;
use Maarheeze\Compass\Collector\CollectionRunId;
use Maarheeze\Compass\Collector\Forge\Jobs\CollectSites;

class SyncForgeCommand extends Command
{
    public const string SIGNATURE = 'compass:sync-forge';

    public function handle(Dispatcher $dispatcher): void
    {
        $dispatcher->dispatch(new CollectSites(CollectionRunId::generate(), null));
    }

    protected function configure(): void
    {
        $this
            ->setName(self::SIGNATURE)
            ->setDescription('Collect the sites from Forge');
    }
}
