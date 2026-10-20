<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Console;

use Illuminate\Console\Command;
use Maarheeze\Compass\Collector\CollectionRunId;
use Maarheeze\Compass\Collector\GitHub\RepositoryCollector;

class SyncGitHubCommand extends Command
{
    public const string SIGNATURE = 'compass:sync-github';

    public function handle(
        RepositoryCollector $repositoryCollector,
    ): void {
        $repositoryCollector->collect(CollectionRunId::generate());
    }

    protected function configure(): void
    {
        $this
            ->setName(self::SIGNATURE)
            ->setDescription('Collect the repositories of the organization from GitHub');
    }
}
