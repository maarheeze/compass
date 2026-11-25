<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Drivers\GitHub;

use Generator;
use Maarheeze\Compass\Collections\CollectedIssueCollection;
use Maarheeze\Compass\Collections\CollectedPullRequestCollection;
use Maarheeze\Compass\Collections\CollectedRepositoryCollection;
use Maarheeze\Compass\Collector\CollectingFailed;
use Maarheeze\Compass\Collector\Composer\ComposerFilesCollector;
use Maarheeze\Compass\Collector\Dependabot\DependabotFilesCollector;
use Maarheeze\Compass\Collector\Issues\IssuesCollector;
use Maarheeze\Compass\Collector\Issues\IssueTypeMapping;
use Maarheeze\Compass\Collector\PullRequests\PullRequestsCollector;
use Maarheeze\Compass\Collector\Repositories\RepositoriesCollector;
use Maarheeze\Compass\Collector\RequestBudgetExhausted;

readonly class GitHubDriver implements
    ComposerFilesCollector,
    DependabotFilesCollector,
    IssuesCollector,
    PullRequestsCollector,
    RepositoriesCollector
{
    public function __construct(
        private GitHubClient $gitHubClient,
        private string $organisation,
        private IssueTypeMapping $issueTypeMapping,
    ) {
    }

    /**
     * @throws CollectingFailed
     * @throws RequestBudgetExhausted
     */
    public function content(string $repositoryName, string $version): string
    {
        $blobQuery = new BlobQuery($this->gitHubClient, $this->organisation);

        try {
            return $blobQuery->text($repositoryName, $version);
        } catch (GitHubQueryFailed $gitHubQueryFailed) {
            throw CollectingFailed::fromThrowable($gitHubQueryFailed);
        }
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

    /**
     * @return Generator<int, CollectedPullRequestCollection>
     *
     * @throws CollectingFailed
     * @throws RequestBudgetExhausted
     */
    public function pullRequests(): Generator
    {
        $issuesAndPullRequestsQuery = new IssuesAndPullRequestsQuery($this->gitHubClient, $this->organisation);

        try {
            yield from $issuesAndPullRequestsQuery->pullRequests();
        } catch (GitHubQueryFailed $gitHubQueryFailed) {
            throw CollectingFailed::fromThrowable($gitHubQueryFailed);
        }
    }

    /**
     * @return Generator<int, CollectedRepositoryCollection>
     *
     * @throws CollectingFailed
     * @throws RequestBudgetExhausted
     */
    public function repositories(): Generator
    {
        $repositoriesQuery = new RepositoriesQuery($this->gitHubClient, $this->organisation);

        try {
            foreach ($repositoriesQuery->pages() as $repositoriesQueryResult) {
                yield $repositoriesQueryResult->collectedRepositories;
            }
        } catch (GitHubQueryFailed $gitHubQueryFailed) {
            throw CollectingFailed::fromThrowable($gitHubQueryFailed);
        }
    }
}
