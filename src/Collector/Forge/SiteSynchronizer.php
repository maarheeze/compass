<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Forge;

use Carbon\CarbonImmutable;
use Maarheeze\Compass\Collections\CollectedSiteCollection;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Uuid\Uuid;

readonly class SiteSynchronizer
{
    public function markUncollectedMissing(string $source, Uuid $runId): void
    {
        Site::query()
            ->ofSource($source)
            ->notReturnedByRun($runId)
            ->collected()
            ->update(['missing_at' => CarbonImmutable::now()]);
    }

    public function storeSites(
        CollectedSiteCollection $collectedSites,
        string $source,
        int $autoDeployGraceMinutes,
        Uuid $runId,
    ): void {
        $siteValues = [];

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
                'collected_at' => CarbonImmutable::now(),
                'missing_at' => null,
                'missing_ignored_at' => null,
            ];
        }

        Site::query()->upsert($siteValues, ['source', 'external_id']);
    }
}
