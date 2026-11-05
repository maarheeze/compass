<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\GitHub\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Log\Context\Repository as Context;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use JsonException;
use Maarheeze\Compass\Collector\CollectingFailed;
use Maarheeze\Compass\Collector\CollectorLogger;
use Maarheeze\Compass\Collector\Composer\ComposerFilesCollectorManager;
use Maarheeze\Compass\Collector\Composer\Json\ComposerJsonReader;
use Maarheeze\Compass\Collector\Composer\Json\ComposerJsonSynchronizer;
use Maarheeze\Compass\Collector\Events\ComposerJsonChanged;
use Maarheeze\Compass\Models\Repository;
use Throwable;

class CollectComposerJson implements ShouldQueue
{
    use Batchable;
    use InteractsWithQueue;
    use Queueable;
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
        ComposerJsonReader $composerJsonReader,
        ComposerJsonSynchronizer $composerJsonSynchronizer,
        Context $context,
        Dispatcher $events,
    ): void {
        $composerFilesCollector = $composerFilesCollectorManager->collector($this->source);

        $context->add('repository', $this->repository->name);

        $oid = $this->repository->composer_json_oid;

        if ($oid === null) {
            $composerJsonSynchronizer->delete($this->repository);

            $collectorLogger->debug('Composer json deleted');

            $events->dispatch(new ComposerJsonChanged($this->repository));

            return;
        }

        $composerJsonSummary = $composerJsonReader->read($composerFilesCollector->content($this->repository->name, $oid));
        $composerJsonSynchronizer->store($this->repository, $oid, $composerJsonSummary);

        $collectorLogger->debug('Composer json collected');

        $events->dispatch(new ComposerJsonChanged($this->repository));
    }
}
