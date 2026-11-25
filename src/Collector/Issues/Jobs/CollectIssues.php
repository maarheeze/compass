<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Issues\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Maarheeze\Compass\Collector\CollectingFailed;
use Maarheeze\Compass\Collector\CollectorLogger;
use Maarheeze\Compass\Collector\Concerns\RetriesWhileLocked;
use Maarheeze\Compass\Collector\Issues\IssuesCollector;
use Maarheeze\Compass\Collector\Issues\IssueSynchroniser;
use Maarheeze\Compass\Collector\LockedJob;
use Maarheeze\Compass\Collector\RequestBudgetExhausted;
use Maarheeze\Compass\Collector\SourceDrivers;
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
     * @throws RequestBudgetExhausted
     */
    public function handle(
        CollectorLogger $collectorLogger,
        IssueSynchroniser $issueSynchroniser,
        SourceDrivers $sourceDrivers,
    ): void {
        $issuesCollector = $sourceDrivers->collector($this->source, IssuesCollector::class);

        $collectorLogger->info('Issues collector started');

        foreach ($issuesCollector->issues() as $collectedIssues) {
            $issueSynchroniser->storeIssues($collectedIssues, $this->source, $this->runId);

            $collectorLogger->debug('Issues page collected', [
                'issue_count' => $collectedIssues->count(),
            ]);
        }

        $issueSynchroniser->deleteUncollected($this->source, $this->runId);

        $collectorLogger->info('Issues collector ended');
    }

    public function lockKey(): string
    {
        return $this->source;
    }
}
