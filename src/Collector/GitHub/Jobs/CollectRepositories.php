<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\GitHub\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Maarheeze\Compass\Collector\CollectionRunId;
use Maarheeze\Compass\Collector\GitHub\GitHubQueryFailed;
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
     * @throws ConnectionException
     * @throws GitHubQueryFailed
     * @throws RequestException
     */
    public function handle(
        RepositoriesQuery $repositoriesQuery,
        RepositorySynchronizer $repositorySynchronizer,
        Dispatcher $dispatcher,
    ): void {
        $repositoriesQueryResult = $repositoriesQuery->page($this->cursor);

        $repositorySynchronizer->storeRepositories(
            $repositoriesQueryResult->gitHubRepositories,
            $this->collectionRunId,
        );

        if ($repositoriesQueryResult->nextCursor !== null) {
            $dispatcher->dispatch(new self($this->collectionRunId, $repositoriesQueryResult->nextCursor));

            return;
        }

        $repositorySynchronizer->updateInactive($this->collectionRunId);
    }
}
