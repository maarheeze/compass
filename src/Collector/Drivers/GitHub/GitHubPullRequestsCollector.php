<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Drivers\GitHub;

use Generator;
use Maarheeze\Compass\Collections\CollectedPullRequestCollection;
use Maarheeze\Compass\Collector\CollectingFailed;
use Maarheeze\Compass\Collector\PullRequests\PullRequestsCollector;

readonly class GitHubPullRequestsCollector implements PullRequestsCollector
{
    public function __construct(
        private GitHubClient $gitHubClient,
        private string $organization,
    ) {
    }

    /**
     * @return Generator<int, CollectedPullRequestCollection>
     *
     * @throws CollectingFailed
     */
    public function pullRequests(): Generator
    {
        $issuesAndPullRequestsQuery = new IssuesAndPullRequestsQuery($this->gitHubClient, $this->organization);

        try {
            yield from $issuesAndPullRequestsQuery->pullRequests();
        } catch (GitHubQueryFailed $gitHubQueryFailed) {
            throw CollectingFailed::fromThrowable($gitHubQueryFailed);
        }
    }
}
