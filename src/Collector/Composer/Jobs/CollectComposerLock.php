<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Composer\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Log\Context\Repository as Context;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use JsonException;
use Maarheeze\Compass\Collector\CollectingFailed;
use Maarheeze\Compass\Collector\CollectorLogger;
use Maarheeze\Compass\Collector\Composer\ComposerFilesCollectorManager;
use Maarheeze\Compass\Collector\Composer\Lock\ComposerLockReader;
use Maarheeze\Compass\Collector\Composer\Lock\ComposerLockSynchronizer;
use Maarheeze\Compass\Collector\Concerns\RetriesWhileLocked;
use Maarheeze\Compass\Collector\Events\ComposerLockChanged;
use Maarheeze\Compass\Collector\LockedJob;
use Maarheeze\Compass\Models\Repository;
use Throwable;

class CollectComposerLock implements LockedJob
{
    use Batchable;
    use InteractsWithQueue;
    use Queueable;
    use RetriesWhileLocked;
    use SerializesModels;

    public function __construct(
        public readonly Repository $repository,
        public readonly string $source,
    ) {
    }

    /**
     * @throws CollectingFailed
     * @throws JsonException
     * @throws Throwable
     */
    public function handle(
        CollectorLogger $collectorLogger,
        ComposerFilesCollectorManager $composerFilesCollectorManager,
        ComposerLockReader $composerLockReader,
        ComposerLockSynchronizer $composerLockSynchronizer,
        Context $context,
        Dispatcher $events,
    ): void {
        $composerFilesCollector = $composerFilesCollectorManager->collector($this->source);

        $context->add('repository', $this->repository->name);

        $version = $this->repository->composer_lock_version;

        if ($version === null) {
            $composerLockSynchronizer->delete($this->repository);

            $collectorLogger->debug('Composer lock deleted');

            $events->dispatch(new ComposerLockChanged($this->repository));

            return;
        }

        $composerLockSummary = $composerLockReader->read($composerFilesCollector->content($this->repository->name, $version));
        $composerLockSynchronizer->store($this->repository, $version, $composerLockSummary);

        $collectorLogger->debug('Composer lock collected');

        $events->dispatch(new ComposerLockChanged($this->repository));
    }

    public function lockKey(): string
    {
        return $this->repository->id->toString();
    }
}
