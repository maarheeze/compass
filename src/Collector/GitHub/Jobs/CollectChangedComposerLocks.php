<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\GitHub\Jobs;

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
    ) {
    }

    public function handle(CollectorLogger $collectorLogger): void
    {
        $repositoriesWithComposerLockOutOfDate = Repository::query()
            ->returnedByRun($this->runId)
            ->composerLockOutOfDate()
            ->get();

        $collectComposerLockJobs = [];

        foreach ($repositoriesWithComposerLockOutOfDate as $repository) {
            $collectComposerLockJobs[] = new CollectComposerLock($repository);

            $collectorLogger->debug('Composer lock collection queued', ['repository' => $repository->name]);
        }

        Assert::isInstanceOf($this->batch(), Batch::class)->add($collectComposerLockJobs);
    }
}
