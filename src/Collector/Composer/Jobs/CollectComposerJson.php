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
use Maarheeze\Compass\Collector\Composer\Json\ComposerJsonReader;
use Maarheeze\Compass\Collector\Composer\Json\ComposerJsonSynchronizer;
use Maarheeze\Compass\Collector\Concerns\RetriesWhileLocked;
use Maarheeze\Compass\Collector\Events\ComposerJsonChanged;
use Maarheeze\Compass\Collector\LockedJob;
use Maarheeze\Compass\Models\Repository;
use Throwable;

class CollectComposerJson implements LockedJob
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
     * @throws Throwable
     */
    public function handle(
        CollectorLogger $collectorLogger,
        ComposerFilesCollectorManager $composerFilesCollectorManager,
        ComposerJsonReader $composerJsonReader,
        ComposerJsonSynchronizer $composerJsonSynchronizer,
        Context $context,
        Dispatcher $events,
    ): void {
        $composerFilesCollector = $composerFilesCollectorManager->collector($this->repository->source);

        $context->add('repository', $this->repository->name);

        $version = $this->repository->composer_json_version;

        if ($version === null) {
            $composerJsonSynchronizer->delete($this->repository);

            $collectorLogger->debug('Composer json deleted');

            $events->dispatch(new ComposerJsonChanged($this->repository));

            return;
        }

        $composerJsonSummary = $composerJsonReader->read($composerFilesCollector->content($this->repository->name, $version));
        $composerJsonSynchronizer->store($this->repository, $version, $composerJsonSummary);

        $collectorLogger->debug('Composer json collected');

        $events->dispatch(new ComposerJsonChanged($this->repository));
    }

    public function lockKey(): string
    {
        return $this->repository->id->toString();
    }
}
