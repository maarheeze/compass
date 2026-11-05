<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\GitHub\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Maarheeze\Compass\Collector\CollectingFailed;
use Maarheeze\Compass\Collector\CollectorLogger;
use Maarheeze\Compass\Collector\GitHub\IssueSynchronizer;
use Maarheeze\Compass\Collector\Issues\IssuesCollectorManager;
use Maarheeze\Uuid\Uuid;

class CollectIssues implements ShouldQueue
{
    use Batchable;
    use InteractsWithQueue;
    use Queueable;

    public function __construct(
        private readonly Uuid $runId,
        private readonly string $source,
    ) {
    }

    /**
     * @throws CollectingFailed
     */
    public function handle(
        CollectorLogger $collectorLogger,
        IssueSynchronizer $issueSynchronizer,
        IssuesCollectorManager $issuesCollectorManager,
    ): void {
        $issuesCollector = $issuesCollectorManager->collector($this->source);

        $collectorLogger->info('Issues collector started');

        foreach ($issuesCollector->issues() as $collectedIssues) {
            $issueSynchronizer->storeIssues($collectedIssues, $this->runId);

            $collectorLogger->debug('Issues page collected', [
                'issue_count' => $collectedIssues->count(),
            ]);
        }

        $issueSynchronizer->deleteUncollected($this->runId);

        $collectorLogger->info('Issues collector ended');
    }
}
