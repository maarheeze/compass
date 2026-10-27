<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\GitHub\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maarheeze\Compass\Collector\GitHub\GitHubQueryFailed;
use Maarheeze\Compass\Collector\GitHub\RepositoriesQuery;
use Maarheeze\Compass\Collector\GitHub\RepositorySynchronizer;
use Maarheeze\Compass\Collector\RunId;

class CollectRepositories implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly RunId $runId,
        private readonly ?string $cursor,
    ) {
    }

    /**
     * @throws GitHubQueryFailed
     */
    public function handle(RepositoriesQuery $repositoriesQuery, RepositorySynchronizer $repositorySynchronizer): void
    {
        $repositoriesQueryResult = $repositoriesQuery->page($this->cursor);

        $repositorySynchronizer->storeRepositories(
            $repositoriesQueryResult->gitHubRepositories,
            $this->runId,
        );

        if ($repositoriesQueryResult->nextCursor !== null) {
            $this->prependToChain(new self($this->runId, $repositoriesQueryResult->nextCursor));

            return;
        }

        $repositorySynchronizer->markUncollectedMissing($this->runId);
    }
}
