<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\GitHub\Jobs;

use Illuminate\Bus\Batch;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Maarheeze\Compass\Collector\CollectingFailed;
use Maarheeze\Compass\Collector\CollectorLogger;
use Maarheeze\Compass\Collector\Events\PackageJsonChanged;
use Maarheeze\Compass\Collector\Events\RepositoriesCollected;
use Maarheeze\Compass\Collector\GitHub\RepositorySynchronizer;
use Maarheeze\Compass\Collector\Repositories\RepositoriesCollectorManager;
use Maarheeze\Uuid\Uuid;
use Webmozart\Assert\Assert;

class CollectRepositories implements ShouldQueue
{
    use Batchable;
    use InteractsWithQueue;
    use Queueable;

    public function __construct(
        private readonly Uuid $runId,
        private readonly string $source,
    ) {
    }

    /**
     * @throws CollectingFailed
     */
    public function handle(
        CollectorLogger $collectorLogger,
        Dispatcher $events,
        RepositoriesCollectorManager $repositoriesCollectorManager,
        RepositorySynchronizer $repositorySynchronizer,
    ): void {
        $repositoriesCollector = $repositoriesCollectorManager->collector($this->source);

        $collectorLogger->info('Repositories collector started');

        foreach ($repositoriesCollector->repositories() as $collectedRepositories) {
            $repositoriesWithChangedPackageJson = $repositorySynchronizer->storeRepositories(
                $collectedRepositories,
                $this->source,
                $this->runId,
            );

            foreach ($repositoriesWithChangedPackageJson as $repositoryWithChangedPackageJson) {
                $events->dispatch(new PackageJsonChanged($repositoryWithChangedPackageJson));
            }

            $collectorLogger->debug('Repositories page collected', [
                'repository_count' => $collectedRepositories->count(),
            ]);
        }

        $repositorySynchronizer->markUncollectedMissing($this->source, $this->runId);

        $events->dispatch(new RepositoriesCollected());

        $collectorLogger->info('Repositories collector ended');

        Assert::isInstanceOf($this->batch(), Batch::class)
            ->add([
                new CollectChangedComposerJsons($this->runId, $this->source),
                new CollectChangedComposerLocks($this->runId, $this->source),
                new CollectChangedDependabotYmls($this->runId, $this->source),
            ]);
    }
}
