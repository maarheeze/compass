<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\GitHub\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maarheeze\Compass\Collector\CollectionRunId;
use Maarheeze\Compass\Collector\GitHub\GitHubQueryFailed;
use Maarheeze\Compass\Collector\GitHub\IssueSynchronizer;
use Maarheeze\Compass\Collector\GitHub\RepositoriesQuery;
use Maarheeze\Compass\Collector\GitHub\RepositorySynchronizer;

class CollectRepositories implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly CollectionRunId $collectionRunId,
        private readonly ?string $cursor,
    ) {
    }

    /**
     * @throws GitHubQueryFailed
     */
    public function handle(
        IssueSynchronizer $issueSynchronizer,
        RepositoriesQuery $repositoriesQuery,
        RepositorySynchronizer $repositorySynchronizer,
    ): void {
        $repositoriesQueryResult = $repositoriesQuery->page($this->cursor);

        $repositorySynchronizer->storeRepositories(
            $repositoriesQueryResult->gitHubRepositories,
            $this->collectionRunId,
        );
        $issueSynchronizer->storeIssues(
            $repositoriesQueryResult->gitHubRepositories,
            $this->collectionRunId,
        );

        if ($repositoriesQueryResult->nextCursor !== null) {
            $this->prependToChain(new self($this->collectionRunId, $repositoriesQueryResult->nextCursor));

            return;
        }

        $repositorySynchronizer->deleteUncollected($this->collectionRunId);
        $issueSynchronizer->deleteUncollected($this->collectionRunId);
    }
}
