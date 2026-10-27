<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\GitHub\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Log\Context\Repository as Context;
use Illuminate\Queue\SerializesModels;
use JsonException;
use Maarheeze\Compass\Collector\CollectorLogger;
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
        CollectorLogger $collectorLogger,
        ComposerLockReader $composerLockReader,
        ComposerLockSynchronizer $composerLockSynchronizer,
        Context $context,
    ): void {
        $context->add('repository', $this->repository->name);

        $oid = $this->repository->composer_lock_oid;

        if ($oid === null) {
            $composerLockSynchronizer->delete($this->repository);

            $collectorLogger->debug('Composer lock deleted');

            return;
        }

        $composerLockSummary = $composerLockReader->read($blobQuery->text($this->repository->name, $oid));
        $composerLockSynchronizer->store($this->repository, $oid, $composerLockSummary);

        $collectorLogger->debug('Composer lock collected');
    }
}
