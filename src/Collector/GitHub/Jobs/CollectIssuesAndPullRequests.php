<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\GitHub\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Maarheeze\Compass\Collector\CollectorLogger;
use Maarheeze\Compass\Collector\GitHub\GitHubQueryFailed;
use Maarheeze\Compass\Collector\GitHub\IssuesAndPullRequestsQuery;
use Maarheeze\Compass\Collector\GitHub\IssueSynchronizer;
use Maarheeze\Compass\Collector\GitHub\PullRequestSynchronizer;
use Maarheeze\Uuid\Uuid;

class CollectIssuesAndPullRequests implements ShouldQueue
{
    use Batchable;
    use InteractsWithQueue;
    use Queueable;

    public function __construct(
        private readonly Uuid $runId,
    ) {
    }

    /**
     * @throws GitHubQueryFailed
     */
    public function handle(
        CollectorLogger $collectorLogger,
        IssuesAndPullRequestsQuery $issuesAndPullRequestsQuery,
        IssueSynchronizer $issueSynchronizer,
        PullRequestSynchronizer $pullRequestSynchronizer,
    ): void {
        $collectorLogger->info('Issues and pull requests collection started');

        foreach ($issuesAndPullRequestsQuery->pages() as $issuesAndPullRequestsQueryResult) {
            $issueSynchronizer->storeIssues($issuesAndPullRequestsQueryResult->gitHubIssues, $this->runId);
            $pullRequestSynchronizer->storePullRequests(
                $issuesAndPullRequestsQueryResult->gitHubPullRequests,
                $this->runId,
            );

            $collectorLogger->debug('Issues and pull requests page collected', [
                'issue_count' => $issuesAndPullRequestsQueryResult->gitHubIssues->count(),
                'pull_request_count' => $issuesAndPullRequestsQueryResult->gitHubPullRequests->count(),
            ]);
        }

        $issueSynchronizer->deleteUncollected($this->runId);
        $pullRequestSynchronizer->deleteUncollected($this->runId);

        $collectorLogger->info('Issues and pull requests collection ended');
    }
}
