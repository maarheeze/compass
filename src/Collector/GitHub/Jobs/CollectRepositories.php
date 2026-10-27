<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\GitHub\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maarheeze\Compass\Collector\CollectorLogger;
use Maarheeze\Compass\Collector\GitHub\GitHubQueryFailed;
use Maarheeze\Compass\Collector\GitHub\RepositoriesQuery;
use Maarheeze\Compass\Collector\GitHub\RepositorySynchronizer;
use Maarheeze\Compass\Collector\RunId;

class CollectRepositories implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly RunId $runId,
    ) {
    }

    /**
     * @throws GitHubQueryFailed
     */
    public function handle(
        CollectorLogger $collectorLogger,
        RepositoriesQuery $repositoriesQuery,
        RepositorySynchronizer $repositorySynchronizer,
    ): void {
        $collectorLogger->info('Repositories collection started');

        foreach ($repositoriesQuery->pages() as $repositoriesQueryResult) {
            $repositorySynchronizer->storeRepositories(
                $repositoriesQueryResult->gitHubRepositories,
                $this->runId,
            );

            $collectorLogger->debug('Repositories page collected', [
                'repository_count' => $repositoriesQueryResult->gitHubRepositories->count(),
            ]);
        }

        $repositorySynchronizer->markUncollectedMissing($this->runId);

        $collectorLogger->info('Repositories collection ended');
    }
}
