<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Forge;

use Carbon\CarbonImmutable;
use Maarheeze\Compass\Collections\ForgeSiteCollection;
use Maarheeze\Compass\Collector\CollectionRunId;
use Maarheeze\Compass\Models\Site;

readonly class SiteSynchronizer
{
    public function markUncollectedMissing(CollectionRunId $collectionRunId): void
    {
        Site::query()
            ->whereNot('collection_run_id', $collectionRunId)
            ->whereNull('missing_at')
            ->update(['missing_at' => CarbonImmutable::now()]);
    }

    public function storeSites(ForgeSiteCollection $forgeSites, CollectionRunId $collectionRunId): void
    {
        $siteValues = [];

        foreach ($forgeSites as $forgeSite) {
            $siteValues[] = [
                'forge_id' => $forgeSite->forgeId,
                'name' => $forgeSite->name,
                'url' => $forgeSite->url,
                'status' => $forgeSite->status,
                'php_version' => $forgeSite->phpVersion,
                'repository_url' => $forgeSite->repositoryUrl,
                'repository_branch' => $forgeSite->repositoryBranch,
                'server_name' => $forgeSite->serverName,
                'server_slug' => $forgeSite->serverSlug,
                'server_ip_address' => $forgeSite->serverIpAddress,
                'deployment_status' => $forgeSite->latestDeployment?->status,
                'deployed_commit_hash' => $forgeSite->latestDeployment?->commitHash,
                'deployed_commit_message' => $forgeSite->latestDeployment?->commitMessage,
                'deployment_started_at' => $forgeSite->latestDeployment?->startedAt,
                'deployment_ended_at' => $forgeSite->latestDeployment?->endedAt,
                'collection_run_id' => $collectionRunId,
                'collected_at' => CarbonImmutable::now(),
                'missing_at' => null,
                'missing_ignored_at' => null,
            ];
        }

        Site::query()->upsert($siteValues, ['forge_id']);
    }
}
