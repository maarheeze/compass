<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Console;

use Illuminate\Console\Command;
use Illuminate\Contracts\Bus\Dispatcher;
use Maarheeze\Compass\Collector\CollectionRunId;
use Maarheeze\Compass\Collector\GitHub\Jobs\CollectChangedComposerJsons;
use Maarheeze\Compass\Collector\GitHub\Jobs\CollectChangedComposerLocks;
use Maarheeze\Compass\Collector\GitHub\Jobs\CollectRepositories;

class SyncGitHubCommand extends Command
{
    public const string SIGNATURE = 'compass:sync-github';

    public function handle(Dispatcher $dispatcher): void
    {
        $collectionRunId = CollectionRunId::generate();

        $collectRepositories = new CollectRepositories($collectionRunId, null);
        $collectRepositories->chain([
            new CollectChangedComposerJsons($collectionRunId),
            new CollectChangedComposerLocks($collectionRunId),
        ]);
        $dispatcher->dispatch($collectRepositories);
    }

    protected function configure(): void
    {
        $this
            ->setName(self::SIGNATURE)
            ->setDescription('Collect the repositories of the organization from GitHub');
    }
}
