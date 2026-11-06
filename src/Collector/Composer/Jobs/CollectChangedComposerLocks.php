<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Composer\Jobs;

use Illuminate\Bus\Batch;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Maarheeze\Compass\Collector\CollectorLogger;
use Maarheeze\Compass\Models\Repository;
use Maarheeze\Uuid\Uuid;
use Webmozart\Assert\Assert;

class CollectChangedComposerLocks implements ShouldQueue
{
    use Batchable;
    use InteractsWithQueue;
    use Queueable;

    public function __construct(
        private readonly Uuid $runId,
        private readonly string $source,
    ) {
    }

    public function handle(CollectorLogger $collectorLogger): void
    {
        $batch = Assert::isInstanceOf($this->batch(), Batch::class);
        $repositoryWithComposerLockOutOfDateChunks = Repository::query()
            ->returnedByRun($this->runId)
            ->composerLockOutOfDate()
            ->lazyById()
            ->chunk(100);

        foreach ($repositoryWithComposerLockOutOfDateChunks as $repositoriesWithComposerLockOutOfDate) {
            $collectComposerLockJobs = [];

            foreach ($repositoriesWithComposerLockOutOfDate as $repository) {
                $collectComposerLockJobs[] = new CollectComposerLock($repository, $this->source);

                $collectorLogger->debug('Composer lock collection queued', ['repository' => $repository->name]);
            }

            $batch->add($collectComposerLockJobs);
        }
    }
}
