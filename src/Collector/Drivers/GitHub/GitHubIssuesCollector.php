<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Drivers\GitHub;

use Generator;
use Maarheeze\Compass\Collections\CollectedIssueCollection;
use Maarheeze\Compass\Collector\CollectingFailed;
use Maarheeze\Compass\Collector\Issues\IssuesCollector;
use Maarheeze\Compass\Collector\Issues\IssueTypeMapping;
use Maarheeze\Compass\Collector\RequestBudgetExhausted;

readonly class GitHubIssuesCollector implements IssuesCollector
{
    public function __construct(
        private GitHubClient $gitHubClient,
        private string $organisation,
        private IssueTypeMapping $issueTypeMapping,
    ) {
    }

    /**
     * @return Generator<int, CollectedIssueCollection>
     *
     * @throws CollectingFailed
     * @throws RequestBudgetExhausted
     */
    public function issues(): Generator
    {
        $issuesAndPullRequestsQuery = new IssuesAndPullRequestsQuery($this->gitHubClient, $this->organisation);

        try {
            yield from $issuesAndPullRequestsQuery->issues($this->issueTypeMapping);
        } catch (GitHubQueryFailed $gitHubQueryFailed) {
            throw CollectingFailed::fromThrowable($gitHubQueryFailed);
        }
    }
}
