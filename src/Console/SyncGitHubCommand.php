<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Console;

use Illuminate\Console\Command;
use Maarheeze\Compass\Collector\CollectionRunId;
use Maarheeze\Compass\Collector\RepositoryCollector;

class SyncGitHubCommand extends Command
{
    public const string SIGNATURE = 'compass:sync-github';

    /** @var string $description */
    protected $description = 'Collect the repositories of the organization from GitHub';

    /** @var string $signature */
    protected $signature = self::SIGNATURE;

    public function handle(
        RepositoryCollector $repositoryCollector,
    ): void {
        $repositoryCollector->collect(CollectionRunId::generate());
    }
}
