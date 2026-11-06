<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Dependabot\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Log\Context\Repository as Context;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maarheeze\Compass\Collector\CollectingFailed;
use Maarheeze\Compass\Collector\CollectorLogger;
use Maarheeze\Compass\Collector\Dependabot\DependabotFilesCollectorManager;
use Maarheeze\Compass\Collector\Dependabot\DependabotYmlReader;
use Maarheeze\Compass\Collector\Dependabot\DependabotYmlSynchronizer;
use Maarheeze\Compass\Collector\Events\DependabotYmlChanged;
use Maarheeze\Compass\Models\Repository;
use Symfony\Component\Yaml\Exception\ParseException;

class CollectDependabotYml implements ShouldQueue
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
     * @throws ParseException
     */
    public function handle(
        CollectorLogger $collectorLogger,
        Context $context,
        Dispatcher $events,
        DependabotFilesCollectorManager $dependabotFilesCollectorManager,
        DependabotYmlReader $dependabotYmlReader,
        DependabotYmlSynchronizer $dependabotYmlSynchronizer,
    ): void {
        $dependabotFilesCollector = $dependabotFilesCollectorManager->collector($this->source);

        $context->add('repository', $this->repository->name);

        $version = $this->repository->dependabot_yml_version;

        if ($version === null) {
            $dependabotYmlSynchronizer->delete($this->repository);

            $collectorLogger->debug('Dependabot yml deleted');

            $events->dispatch(new DependabotYmlChanged($this->repository));

            return;
        }

        $packageEcosystems = $dependabotYmlReader->packageEcosystems(
            $dependabotFilesCollector->content($this->repository->name, $version),
        );
        $dependabotYmlSynchronizer->store($this->repository, $version, $packageEcosystems);

        $collectorLogger->debug('Dependabot yml collected');

        $events->dispatch(new DependabotYmlChanged($this->repository));
    }
}
