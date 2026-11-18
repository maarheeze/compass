<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Drivers\GitHub;

use Generator;
use Maarheeze\Compass\Collections\CollectedIssueCollection;
use Maarheeze\Compass\Collector\CollectingFailed;
use Maarheeze\Compass\Collector\Issues\IssuesCollector;

readonly class GitHubIssuesCollector implements IssuesCollector
{
    public function __construct(
        private GitHubClient $gitHubClient,
        private string $organisation,
    ) {
    }

    /**
     * @return Generator<int, CollectedIssueCollection>
     *
     * @throws CollectingFailed
     */
    public function issues(): Generator
    {
        $issuesAndPullRequestsQuery = new IssuesAndPullRequestsQuery($this->gitHubClient, $this->organisation);

        try {
            yield from $issuesAndPullRequestsQuery->issues();
        } catch (GitHubQueryFailed $gitHubQueryFailed) {
            throw CollectingFailed::fromThrowable($gitHubQueryFailed);
        }
    }
}
