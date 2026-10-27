<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Console;

use Illuminate\Console\Command;
use Maarheeze\Compass\Collector\GitHub\Jobs\CollectRepositories;
use Maarheeze\Compass\Collector\RunId;
use Maarheeze\Compass\Collector\RunRecorder;
use Maarheeze\Compass\Collector\RunType;

class SyncGitHubCommand extends Command
{
    public const string SIGNATURE = 'compass:github-sync';

    public function handle(RunRecorder $runRecorder): void
    {
        $runId = RunId::generate();

        $runRecorder->start($runId, RunType::GITHUB_SYNC, new CollectRepositories($runId));
    }

    protected function configure(): void
    {
        $this
            ->setName(self::SIGNATURE)
            ->setDescription('Collect the required data from GitHub and synchronize the database');
    }
}
