<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\GitHub\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maarheeze\Compass\Collector\RunId;
use Maarheeze\Compass\Models\Repository;

class CollectChangedComposerLocks implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly RunId $runId,
    ) {
    }

    public function handle(Dispatcher $dispatcher): void
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

        foreach ($repositoriesWithChangedComposerLock->merge($repositoriesWithRemovedComposerLock) as $repository) {
            $dispatcher->dispatch(new CollectComposerLock($repository));
        }
    }
}
