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
use Maarheeze\Uuid\Uuid;

class CollectIssues implements ShouldQueue
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
    ): void {
        $collectorLogger->info('Issues collector started');

        foreach ($issuesAndPullRequestsQuery->pages() as $issuesAndPullRequestsQueryResult) {
            $issueSynchronizer->storeIssues($issuesAndPullRequestsQueryResult->gitHubIssues, $this->runId);

            $collectorLogger->debug('Issues page collected', [
                'issue_count' => $issuesAndPullRequestsQueryResult->gitHubIssues->count(),
            ]);
        }

        $issueSynchronizer->deleteUncollected($this->runId);

        $collectorLogger->info('Issues collector ended');
    }
}
