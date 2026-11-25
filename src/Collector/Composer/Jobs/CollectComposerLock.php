<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Composer\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Log\Context\Repository as Context;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use JsonException;
use Maarheeze\Compass\Alerts\AlertSubject;
use Maarheeze\Compass\Collector\CollectingFailed;
use Maarheeze\Compass\Collector\CollectorLogger;
use Maarheeze\Compass\Collector\Composer\ComposerFilesCollector;
use Maarheeze\Compass\Collector\Composer\Lock\ComposerLockReader;
use Maarheeze\Compass\Collector\Composer\Lock\ComposerLockSynchroniser;
use Maarheeze\Compass\Collector\Concerns\RetriesWhileLocked;
use Maarheeze\Compass\Collector\Events\ComposerLockChanged;
use Maarheeze\Compass\Collector\RequestBudgetExhausted;
use Maarheeze\Compass\Collector\SourceDrivers;
use Maarheeze\Compass\Collector\SubjectJob;
use Maarheeze\Compass\Models\Repository;
use Throwable;

class CollectComposerLock implements SubjectJob
{
    use Batchable;
    use InteractsWithQueue;
    use Queueable;
    use RetriesWhileLocked;
    use SerializesModels;

    public function __construct(
        public readonly Repository $repository,
    ) {
    }

    /**
     * @throws CollectingFailed
     * @throws JsonException
     * @throws RequestBudgetExhausted
     * @throws Throwable
     */
    public function handle(
        CollectorLogger $collectorLogger,
        ComposerLockReader $composerLockReader,
        ComposerLockSynchroniser $composerLockSynchroniser,
        Context $context,
        Dispatcher $events,
        SourceDrivers $sourceDrivers,
    ): void {
        $composerFilesCollector = $sourceDrivers->collector($this->repository->source, ComposerFilesCollector::class);

        $context->add('repository', $this->repository->name);
        $context->add('file', 'composer.lock');

        $version = $this->repository->composer_lock_version;

        if ($version === null) {
            $composerLockSynchroniser->delete($this->repository);

            $collectorLogger->debug('Composer lock deleted');

            $events->dispatch(new ComposerLockChanged($this->repository));

            return;
        }

        $composerLockSummary = $composerLockReader->read($composerFilesCollector->content($this->repository->name, $version));
        $composerLockSynchroniser->store($this->repository, $version, $composerLockSummary);

        $collectorLogger->debug('Composer lock collected');

        $events->dispatch(new ComposerLockChanged($this->repository));
    }

    public function lockKey(): string
    {
        return $this->repository->id->toString();
    }

    public function skipsSubject(): bool
    {
        return false;
    }

    public function subject(): AlertSubject&Model
    {
        return $this->repository;
    }
}
