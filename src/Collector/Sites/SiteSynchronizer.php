<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Sites;

use Carbon\CarbonImmutable;
use Maarheeze\Compass\Collections\CollectedSiteCollection;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Uuid\Uuid;
use Psr\Clock\ClockInterface;

readonly class SiteSynchronizer
{
    public function __construct(
        private ClockInterface $clock,
    ) {
    }

    public function markUncollectedMissing(string $source, Uuid $runId): void
    {
        $missingAt = CarbonImmutable::instance($this->clock->now());

        Site::query()
            ->ofSource($source)
            ->notReturnedByRun($runId)
            ->collected()
            ->update(['missing_at' => $missingAt]);
    }

    public function storeSites(
        CollectedSiteCollection $collectedSites,
        string $source,
        int $autoDeployGraceMinutes,
        Uuid $runId,
    ): void {
        $siteValues = [];
        $collectedAt = CarbonImmutable::instance($this->clock->now());

        foreach ($collectedSites as $collectedSite) {
            $siteValues[] = [
                'source' => $source,
                'external_id' => $collectedSite->externalId,
                'name' => $collectedSite->name,
                'url' => $collectedSite->url,
                'php_version' => $collectedSite->phpVersion,
                'repository_url' => $collectedSite->repositoryUrl,
                'repository_branch' => $collectedSite->repositoryBranch,
                'auto_deploy' => $collectedSite->autoDeploy,
                'auto_deploy_grace_minutes' => $autoDeployGraceMinutes,
                'server_name' => $collectedSite->serverName,
                'server_ip_address' => $collectedSite->serverIpAddress,
                'deployment_status' => $collectedSite->deploymentStatus,
                'deployed_commit_hash' => $collectedSite->deployedCommitHash,
                'deployment_started_at' => $collectedSite->deploymentStartedAt,
                'deployment_ended_at' => $collectedSite->deploymentEndedAt,
                'run_id' => $runId,
                'collected_at' => $collectedAt,
                'missing_at' => null,
                'missing_ignored_at' => null,
            ];
        }

        Site::query()->upsert($siteValues, ['source', 'external_id']);
    }
}
