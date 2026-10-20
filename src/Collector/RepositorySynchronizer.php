<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector;

use Carbon\CarbonImmutable;
use Maarheeze\Compass\Collections\GitHubRepositoryCollection;
use Maarheeze\Compass\Models\Repository;

readonly class RepositorySynchronizer
{
    public function updateInactive(CollectionRunId $collectionRunId): void
    {
        Repository::query()
            ->whereNot('collection_run_id', $collectionRunId)
            ->update(['is_active' => false]);
    }

    public function storeRepositories(
        GitHubRepositoryCollection $gitHubRepositories,
        CollectionRunId $collectionRunId,
    ): void {
        $repositoryValues = [];

        foreach ($gitHubRepositories as $gitHubRepository) {
            $repositoryValues[] = [
                'name' => $gitHubRepository->name,
                'default_branch' => $gitHubRepository->defaultBranch,
                'collection_run_id' => $collectionRunId,
                'is_active' => true,
                'collected_at' => CarbonImmutable::now(),
            ];
        }

        Repository::query()->upsert($repositoryValues, ['name']);
    }
}
