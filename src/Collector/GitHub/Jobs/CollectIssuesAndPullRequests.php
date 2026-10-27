<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\GitHub\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maarheeze\Compass\Collector\GitHub\GitHubQueryFailed;
use Maarheeze\Compass\Collector\GitHub\IssuesAndPullRequestsQuery;
use Maarheeze\Compass\Collector\GitHub\IssueSynchronizer;
use Maarheeze\Compass\Collector\GitHub\PullRequestSynchronizer;
use Maarheeze\Compass\Collector\RunId;

class CollectIssuesAndPullRequests implements ShouldQueue
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
        IssuesAndPullRequestsQuery $issuesAndPullRequestsQuery,
        IssueSynchronizer $issueSynchronizer,
        PullRequestSynchronizer $pullRequestSynchronizer,
    ): void {
        foreach ($issuesAndPullRequestsQuery->pages() as $issuesAndPullRequestsQueryResult) {
            $issueSynchronizer->storeIssues($issuesAndPullRequestsQueryResult->gitHubIssues, $this->runId);
            $pullRequestSynchronizer->storePullRequests(
                $issuesAndPullRequestsQueryResult->gitHubPullRequests,
                $this->runId,
            );
        }

        $issueSynchronizer->deleteUncollected($this->runId);
        $pullRequestSynchronizer->deleteUncollected($this->runId);
    }
}
