<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\GitHub\Jobs;

use Illuminate\Bus\Batch;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Database\Eloquent\Builder;
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
        $repositoriesWithChangedComposerLock = Repository::query()
            ->where('run_id', $this->runId)
            ->whereNotNull('composer_lock_oid')
            ->whereDoesntHave('composerLock', static function (Builder $composerLockQuery): void {
                $composerLockQuery->whereColumn('oid', 'compass_repositories.composer_lock_oid');
            })
            ->get();

        $repositoriesWithRemovedComposerLock = Repository::query()
            ->where('run_id', $this->runId)
            ->whereNull('composer_lock_oid')
            ->has('composerLock')
            ->get();

        $collectComposerLockJobs = [];

        foreach ($repositoriesWithChangedComposerLock->merge($repositoriesWithRemovedComposerLock) as $repository) {
            $collectComposerLockJobs[] = new CollectComposerLock($repository);

            $collectorLogger->debug('Composer lock collection queued', ['repository' => $repository->name]);
        }

        Assert::isInstanceOf($this->batch(), Batch::class)->add($collectComposerLockJobs);
    }
}
