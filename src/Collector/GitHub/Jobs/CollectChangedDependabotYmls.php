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

class CollectChangedDependabotYmls implements ShouldQueue
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
        $batch = Assert::isInstanceOf($this->batch(), Batch::class);
        $repositoryWithDependabotYmlOutOfDateChunks = Repository::query()
            ->returnedByRun($this->runId)
            ->dependabotYmlOutOfDate()
            ->lazyById()
            ->chunk(100);

        foreach ($repositoryWithDependabotYmlOutOfDateChunks as $repositoriesWithDependabotYmlOutOfDate) {
            $collectDependabotYmlJobs = [];

            foreach ($repositoriesWithDependabotYmlOutOfDate as $repository) {
                $collectDependabotYmlJobs[] = new CollectDependabotYml($repository);

                $collectorLogger->debug('Dependabot yml collection queued', ['repository' => $repository->name]);
            }

            $batch->add($collectDependabotYmlJobs);
        }
    }
}
