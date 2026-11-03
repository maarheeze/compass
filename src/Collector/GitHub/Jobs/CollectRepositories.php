<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\GitHub\Jobs;

use Illuminate\Bus\Batch;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Maarheeze\Compass\Collector\CollectorLogger;
use Maarheeze\Compass\Collector\GitHub\GitHubQueryFailed;
use Maarheeze\Compass\Collector\GitHub\RepositoriesQuery;
use Maarheeze\Compass\Collector\GitHub\RepositorySynchronizer;
use Maarheeze\Uuid\Uuid;
use Webmozart\Assert\Assert;

class CollectRepositories implements ShouldQueue
{
    use Batchable;
    use InteractsWithQueue;
    use Queueable;

    public function __construct(
        private readonly Uuid $runId,
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
        $collectorLogger->info('Repositories collector started');

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

        $collectorLogger->info('Repositories collector ended');

        Assert::isInstanceOf($this->batch(), Batch::class)
            ->add([
                new CollectChangedComposerJsons($this->runId),
                new CollectChangedComposerLocks($this->runId),
                new CollectChangedDependabotYmls($this->runId),
            ]);
    }
}
