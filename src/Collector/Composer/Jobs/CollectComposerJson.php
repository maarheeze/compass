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
use Maarheeze\Compass\Collector\Composer\Json\ComposerJsonReader;
use Maarheeze\Compass\Collector\Composer\Json\ComposerJsonSynchroniser;
use Maarheeze\Compass\Collector\Concerns\RetriesWhileLocked;
use Maarheeze\Compass\Collector\Events\ComposerJsonChanged;
use Maarheeze\Compass\Collector\RequestBudgetExhausted;
use Maarheeze\Compass\Collector\SourceDrivers;
use Maarheeze\Compass\Collector\SubjectJob;
use Maarheeze\Compass\Models\Repository;
use Throwable;

class CollectComposerJson implements SubjectJob
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
        ComposerJsonReader $composerJsonReader,
        ComposerJsonSynchroniser $composerJsonSynchroniser,
        Context $context,
        Dispatcher $events,
        SourceDrivers $sourceDrivers,
    ): void {
        $composerFilesCollector = $sourceDrivers->collector($this->repository->source, ComposerFilesCollector::class);

        $context->add('repository', $this->repository->name);
        $context->add('file', 'composer.json');

        $version = $this->repository->composer_json_version;

        if ($version === null) {
            $composerJsonSynchroniser->delete($this->repository);

            $collectorLogger->debug('Composer json deleted');

            $events->dispatch(new ComposerJsonChanged($this->repository));

            return;
        }

        $composerJsonSummary = $composerJsonReader->read($composerFilesCollector->content($this->repository->name, $version));
        $composerJsonSynchroniser->store($this->repository, $version, $composerJsonSummary);

        $collectorLogger->debug('Composer json collected');

        $events->dispatch(new ComposerJsonChanged($this->repository));
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
