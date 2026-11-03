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
use Maarheeze\Compass\Collector\GitHub\PullRequestSynchronizer;
use Maarheeze\Uuid\Uuid;

class CollectPullRequests implements ShouldQueue
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
        PullRequestSynchronizer $pullRequestSynchronizer,
    ): void {
        $collectorLogger->info('Pull requests collector started');

        foreach ($issuesAndPullRequestsQuery->pages() as $issuesAndPullRequestsQueryResult) {
            $pullRequestSynchronizer->storePullRequests(
                $issuesAndPullRequestsQueryResult->gitHubPullRequests,
                $this->runId,
            );

            $collectorLogger->debug('Pull requests page collected', [
                'pull_request_count' => $issuesAndPullRequestsQueryResult->gitHubPullRequests->count(),
            ]);
        }

        $pullRequestSynchronizer->deleteUncollected($this->runId);

        $collectorLogger->info('Pull requests collector ended');
    }
}
