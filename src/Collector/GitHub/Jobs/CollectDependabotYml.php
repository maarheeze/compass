<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\GitHub\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Log\Context\Repository as Context;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maarheeze\Compass\Collector\CollectorLogger;
use Maarheeze\Compass\Collector\Dependabot\DependabotYmlReader;
use Maarheeze\Compass\Collector\Dependabot\DependabotYmlSynchronizer;
use Maarheeze\Compass\Collector\GitHub\BlobQuery;
use Maarheeze\Compass\Collector\GitHub\GitHubQueryFailed;
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
    ) {
    }

    /**
     * @throws GitHubQueryFailed
     * @throws ParseException
     */
    public function handle(
        BlobQuery $blobQuery,
        CollectorLogger $collectorLogger,
        Context $context,
        DependabotYmlReader $dependabotYmlReader,
        DependabotYmlSynchronizer $dependabotYmlSynchronizer,
    ): void {
        $context->add('repository', $this->repository->name);

        $oid = $this->repository->dependabot_yml_oid;

        if ($oid === null) {
            $dependabotYmlSynchronizer->delete($this->repository);

            $collectorLogger->debug('Dependabot yml deleted');

            return;
        }

        $packageEcosystems = $dependabotYmlReader->packageEcosystems($blobQuery->text($this->repository->name, $oid));
        $dependabotYmlSynchronizer->store($this->repository, $oid, $packageEcosystems);

        $collectorLogger->debug('Dependabot yml collected');
    }
}
