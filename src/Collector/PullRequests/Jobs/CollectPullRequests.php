<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\PullRequests\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Maarheeze\Compass\Collector\CollectingFailed;
use Maarheeze\Compass\Collector\CollectorLogger;
use Maarheeze\Compass\Collector\Concerns\RetriesWhileLocked;
use Maarheeze\Compass\Collector\LockedJob;
use Maarheeze\Compass\Collector\PullRequests\PullRequestsCollectorManager;
use Maarheeze\Compass\Collector\PullRequests\PullRequestSynchronizer;
use Maarheeze\Uuid\Uuid;

class CollectPullRequests implements LockedJob
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
        PullRequestSynchronizer $pullRequestSynchronizer,
        PullRequestsCollectorManager $pullRequestsCollectorManager,
    ): void {
        $pullRequestsCollector = $pullRequestsCollectorManager->collector($this->source);

        $collectorLogger->info('Pull requests collector started');

        foreach ($pullRequestsCollector->pullRequests() as $collectedPullRequests) {
            $pullRequestSynchronizer->storePullRequests($collectedPullRequests, $this->source, $this->runId);

            $collectorLogger->debug('Pull requests page collected', [
                'pull_request_count' => $collectedPullRequests->count(),
            ]);
        }

        $pullRequestSynchronizer->deleteUncollected($this->source, $this->runId);

        $collectorLogger->info('Pull requests collector ended');
    }

    public function lockKey(): string
    {
        return $this->source;
    }
}
