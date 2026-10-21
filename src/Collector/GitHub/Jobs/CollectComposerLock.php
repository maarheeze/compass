<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\GitHub\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;
use JsonException;
use Maarheeze\Compass\Collector\Composer\Lock\ComposerLockReader;
use Maarheeze\Compass\Collector\Composer\Lock\ComposerLockSynchronizer;
use Maarheeze\Compass\Collector\GitHub\BlobQuery;
use Maarheeze\Compass\Collector\GitHub\GitHubQueryFailed;
use Maarheeze\Compass\Models\Repository;
use Throwable;

class CollectComposerLock implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly Repository $repository,
    ) {
    }

    /**
     * @throws GitHubQueryFailed
     * @throws JsonException
     * @throws Throwable
     */
    public function handle(
        BlobQuery $blobQuery,
        ComposerLockReader $composerLockReader,
        ComposerLockSynchronizer $composerLockSynchronizer,
    ): void {
        $oid = $this->repository->composer_lock_oid;

        if ($oid === null) {
            $composerLockSynchronizer->delete($this->repository);

            return;
        }

        $composerLockSummary = $composerLockReader->read($blobQuery->text($this->repository->name, $oid));
        $composerLockSynchronizer->store($this->repository, $oid, $composerLockSummary);
    }
}
