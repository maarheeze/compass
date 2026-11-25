<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Dependabot\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Log\Context\Repository as Context;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maarheeze\Compass\Alerts\AlertSubject;
use Maarheeze\Compass\Collector\CollectingFailed;
use Maarheeze\Compass\Collector\CollectorLogger;
use Maarheeze\Compass\Collector\Concerns\RetriesWhileLocked;
use Maarheeze\Compass\Collector\Dependabot\DependabotFilesCollectorManager;
use Maarheeze\Compass\Collector\Dependabot\DependabotYmlReader;
use Maarheeze\Compass\Collector\Dependabot\DependabotYmlSynchroniser;
use Maarheeze\Compass\Collector\Events\DependabotYmlChanged;
use Maarheeze\Compass\Collector\RequestBudgetExhausted;
use Maarheeze\Compass\Collector\SubjectJob;
use Maarheeze\Compass\Models\Repository;
use Symfony\Component\Yaml\Exception\ParseException;

class CollectDependabotYml implements SubjectJob
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
     * @throws ParseException
     * @throws RequestBudgetExhausted
     */
    public function handle(
        CollectorLogger $collectorLogger,
        Context $context,
        Dispatcher $events,
        DependabotFilesCollectorManager $dependabotFilesCollectorManager,
        DependabotYmlReader $dependabotYmlReader,
        DependabotYmlSynchroniser $dependabotYmlSynchroniser,
    ): void {
        $dependabotFilesCollector = $dependabotFilesCollectorManager->collector($this->repository->source);

        $context->add('repository', $this->repository->name);

        $version = $this->repository->dependabot_yml_version;

        if ($version === null) {
            $dependabotYmlSynchroniser->delete($this->repository);

            $collectorLogger->debug('Dependabot yml deleted');

            $events->dispatch(new DependabotYmlChanged($this->repository));

            return;
        }

        $packageEcosystems = $dependabotYmlReader->packageEcosystems(
            $dependabotFilesCollector->content($this->repository->name, $version),
        );
        $dependabotYmlSynchroniser->store($this->repository, $version, $packageEcosystems);

        $collectorLogger->debug('Dependabot yml collected');

        $events->dispatch(new DependabotYmlChanged($this->repository));
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
