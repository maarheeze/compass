<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\GitHub;

use Carbon\CarbonImmutable;
use Maarheeze\Compass\Collections\GitHubRepositoryCollection;
use Maarheeze\Compass\Collector\CollectionRunId;
use Maarheeze\Compass\Models\Repository;

readonly class RepositorySynchronizer
{
    public function markUncollectedMissing(CollectionRunId $collectionRunId): void
    {
        Repository::query()
            ->whereNot('collection_run_id', $collectionRunId)
            ->whereNull('missing_at')
            ->update(['missing_at' => CarbonImmutable::now()]);
    }

    public function storeRepositories(
        GitHubRepositoryCollection $gitHubRepositories,
        CollectionRunId $collectionRunId,
    ): void {
        $repositoryValues = [];

        foreach ($gitHubRepositories as $gitHubRepository) {
            $repositoryValues[] = [
                'github_id' => $gitHubRepository->gitHubId,
                'name' => $gitHubRepository->name,
                'url' => $gitHubRepository->url,
                'default_branch' => $gitHubRepository->defaultBranch,
                'last_commit_sha' => $gitHubRepository->lastCommitSha,
                'visibility' => $gitHubRepository->visibility,
                'composer_json_oid' => $gitHubRepository->composerJsonOid,
                'composer_lock_oid' => $gitHubRepository->composerLockOid,
                'collection_run_id' => $collectionRunId,
                'collected_at' => CarbonImmutable::now(),
                'missing_at' => null,
                'missing_ignored_at' => null,
            ];
        }

        Repository::query()->upsert($repositoryValues, ['github_id']);
    }
}
