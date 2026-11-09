<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Composer\Jobs;

use Illuminate\Bus\Batch;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Maarheeze\Compass\Collector\CollectorLogger;
use Maarheeze\Compass\Collector\Concerns\RetriesWhileLocked;
use Maarheeze\Compass\Collector\LockedJob;
use Maarheeze\Compass\Models\Repository;
use Maarheeze\Uuid\Uuid;
use Webmozart\Assert\Assert;

class CollectChangedComposerJsons implements LockedJob
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

    public function handle(CollectorLogger $collectorLogger): void
    {
        $batch = Assert::isInstanceOf($this->batch(), Batch::class);
        $repositoryWithComposerJsonOutOfDateChunks = Repository::query()
            ->returnedByRun($this->runId)
            ->composerJsonOutOfDate()
            ->lazyById()
            ->chunk(100);

        foreach ($repositoryWithComposerJsonOutOfDateChunks as $repositoriesWithComposerJsonOutOfDate) {
            $collectComposerJsonJobs = [];

            foreach ($repositoriesWithComposerJsonOutOfDate as $repository) {
                $collectComposerJsonJobs[] = new CollectComposerJson($repository, $this->source);

                $collectorLogger->debug('Composer json collection queued', ['repository' => $repository->name]);
            }

            $batch->add($collectComposerJsonJobs);
        }
    }

    public function lockKey(): string
    {
        return $this->source;
    }
}
