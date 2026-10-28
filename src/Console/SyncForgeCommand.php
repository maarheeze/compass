<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Console;

use Illuminate\Console\Command;
use Maarheeze\Compass\Collector\Events\ForgeRunSucceeded;
use Maarheeze\Compass\Collector\Forge\Jobs\CollectSites;
use Maarheeze\Compass\Collector\RunRecorder;
use Maarheeze\Compass\Collector\RunType;
use Maarheeze\Uuid\Uuid;

class SyncForgeCommand extends Command
{
    public const string SIGNATURE = 'compass:forge-sync';

    public function handle(RunRecorder $runRecorder): void
    {
        $runId = Uuid::generate();

        $runRecorder->start(
            $runId,
            RunType::FORGE_SYNC,
            new CollectSites($runId),
            new ForgeRunSucceeded($runId),
        );
    }

    protected function configure(): void
    {
        $this
            ->setName(self::SIGNATURE)
            ->setDescription('Collect the sites from Forge, inspect them and synchronize the database');
    }
}
