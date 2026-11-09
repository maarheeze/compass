<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Issues\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Maarheeze\Compass\Collector\CollectingFailed;
use Maarheeze\Compass\Collector\CollectorLogger;
use Maarheeze\Compass\Collector\Concerns\RetriesWhileLocked;
use Maarheeze\Compass\Collector\Issues\IssuesCollectorManager;
use Maarheeze\Compass\Collector\Issues\IssueSynchronizer;
use Maarheeze\Compass\Collector\LockedJob;
use Maarheeze\Uuid\Uuid;

class CollectIssues implements LockedJob
{
    use Batchable;
    use InteractsWithQueue;
    use Queueable;
    use RetriesWhileLocked;

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
            $issueSynchronizer->storeIssues($collectedIssues, $this->source, $this->runId);

            $collectorLogger->debug('Issues page collected', [
                'issue_count' => $collectedIssues->count(),
            ]);
        }

        $issueSynchronizer->deleteUncollected($this->source, $this->runId);

        $collectorLogger->info('Issues collector ended');
    }

    public function lockKey(): string
    {
        return $this->source;
    }
}
