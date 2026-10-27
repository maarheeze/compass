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
        private readonly ?string $pageUrl,
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
        $issuesAndPullRequestsQueryResult = $issuesAndPullRequestsQuery->page($this->pageUrl);

        $issueSynchronizer->storeIssues($issuesAndPullRequestsQueryResult->gitHubIssues, $this->runId);
        $pullRequestSynchronizer->storePullRequests(
            $issuesAndPullRequestsQueryResult->gitHubPullRequests,
            $this->runId,
        );

        if ($issuesAndPullRequestsQueryResult->nextPageUrl !== null) {
            $this->prependToChain(new self($this->runId, $issuesAndPullRequestsQueryResult->nextPageUrl));

            return;
        }

        $issueSynchronizer->deleteUncollected($this->runId);
        $pullRequestSynchronizer->deleteUncollected($this->runId);
    }
}
