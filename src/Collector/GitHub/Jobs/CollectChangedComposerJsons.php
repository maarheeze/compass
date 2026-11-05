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

class CollectChangedComposerJsons implements ShouldQueue
{
    use Batchable;
    use InteractsWithQueue;
    use Queueable;

    public function __construct(
        private readonly Uuid $runId,
        private readonly string $source,
    ) {
    }

    public function handle(CollectorLogger $collectorLogger): void
    {
        $batch = Assert::isInstanceOf($this->batch(), Batch::class);
        $repositoryWithComposerJsonOutOfDateChunks = Repository::query()
            ->returnedByRun($this->runId)
            ->composerJsonOutOfDate()
            ->lazyById()
            ->chunk(100);

        foreach ($repositoryWithComposerJsonOutOfDateChunks as $repositoriesWithComposerJsonOutOfDate) {
            $collectComposerJsonJobs = [];

            foreach ($repositoriesWithComposerJsonOutOfDate as $repository) {
                $collectComposerJsonJobs[] = new CollectComposerJson($repository, $this->source);

                $collectorLogger->debug('Composer json collection queued', ['repository' => $repository->name]);
            }

            $batch->add($collectComposerJsonJobs);
        }
    }
}
