<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Console;

use Illuminate\Console\Command;
use Illuminate\Contracts\Bus\Dispatcher;
use Maarheeze\Compass\Collector\GitHub\Jobs\CollectChangedComposerJsons;
use Maarheeze\Compass\Collector\GitHub\Jobs\CollectChangedComposerLocks;
use Maarheeze\Compass\Collector\GitHub\Jobs\CollectIssuesAndPullRequests;
use Maarheeze\Compass\Collector\GitHub\Jobs\CollectRepositories;
use Maarheeze\Compass\Collector\RunId;

class SyncGitHubCommand extends Command
{
    public const string SIGNATURE = 'compass:github-sync';

    public function handle(Dispatcher $dispatcher): void
    {
        $runId = RunId::generate();

        $collectRepositories = new CollectRepositories($runId, null);
        $collectRepositories->chain([
            new CollectIssuesAndPullRequests($runId, null),
            new CollectChangedComposerJsons($runId),
            new CollectChangedComposerLocks($runId),
        ]);
        $dispatcher->dispatch($collectRepositories);
    }

    protected function configure(): void
    {
        $this
            ->setName(self::SIGNATURE)
            ->setDescription('Collect the required data from GitHub and synchronize the database');
    }
}
