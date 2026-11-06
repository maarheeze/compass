<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Drivers\GitHub;

use Generator;
use Maarheeze\Compass\Collections\CollectedRepositoryCollection;
use Maarheeze\Compass\Collector\CollectingFailed;
use Maarheeze\Compass\Collector\Repositories\RepositoriesCollector;

readonly class GitHubRepositoriesCollector implements RepositoriesCollector
{
    public function __construct(
        private GitHubClient $gitHubClient,
        private string $organization,
    ) {
    }

    /**
     * @return Generator<int, CollectedRepositoryCollection>
     *
     * @throws CollectingFailed
     */
    public function repositories(): Generator
    {
        $repositoriesQuery = new RepositoriesQuery($this->gitHubClient, $this->organization);

        try {
            foreach ($repositoriesQuery->pages() as $repositoriesQueryResult) {
                yield $repositoriesQueryResult->collectedRepositories;
            }
        } catch (GitHubQueryFailed $gitHubQueryFailed) {
            throw CollectingFailed::fromThrowable($gitHubQueryFailed);
        }
    }
}
