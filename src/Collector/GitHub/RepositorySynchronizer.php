<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\GitHub;

use Carbon\CarbonImmutable;
use Maarheeze\Compass\Collections\CollectedRepositoryCollection;
use Maarheeze\Compass\Collections\RepositoryCollection;
use Maarheeze\Compass\Collector\Repositories\CollectedRepository;
use Maarheeze\Compass\Models\Repository;
use Maarheeze\Uuid\Uuid;

readonly class RepositorySynchronizer
{
    public function markUncollectedMissing(string $sourceKey, Uuid $runId): void
    {
        Repository::query()
            ->ofSource($sourceKey)
            ->notReturnedByRun($runId)
            ->collected()
            ->update(['missing_at' => CarbonImmutable::now()]);
    }

    public function storeRepositories(
        CollectedRepositoryCollection $collectedRepositories,
        string $sourceKey,
        Uuid $runId,
    ): RepositoryCollection {
        $storedRepositories = Repository::query()
            ->ofSource($sourceKey)
            ->ofGitHubIds($collectedRepositories->externalIds())
            ->get();
        $repositoryValues = [];

        foreach ($collectedRepositories as $collectedRepository) {
            $repositoryValues[] = [
                'source' => $sourceKey,
                'github_id' => $collectedRepository->externalId,
                'name' => $collectedRepository->name,
                'url' => $collectedRepository->url,
                'default_branch' => $collectedRepository->defaultBranch,
                'last_commit_sha' => $collectedRepository->lastCommitSha,
                'last_commit_committed_at' => $collectedRepository->lastCommitCommittedAt,
                'visibility' => $collectedRepository->visibility,
                'composer_json_oid' => $collectedRepository->composerJsonVersion,
                'composer_lock_oid' => $collectedRepository->composerLockVersion,
                'package_json_oid' => $collectedRepository->packageJsonVersion,
                'dependabot_yml_oid' => $collectedRepository->dependabotYmlVersion,
                'run_id' => $runId,
                'collected_at' => CarbonImmutable::now(),
                'missing_at' => null,
                'missing_ignored_at' => null,
            ];
        }

        Repository::query()->upsert($repositoryValues, ['source', 'github_id']);

        return $this->repositoriesWithChangedPackageJson($collectedRepositories, $sourceKey, $storedRepositories);
    }

    private function repositoriesWithChangedPackageJson(
        CollectedRepositoryCollection $collectedRepositories,
        string $sourceKey,
        RepositoryCollection $storedRepositories,
    ): RepositoryCollection {
        $collectedRepositoriesWithChangedPackageJson = $collectedRepositories->filter(
            static function (CollectedRepository $collectedRepository) use ($storedRepositories): bool {
                $storedRepository = $storedRepositories->firstWhere('github_id', $collectedRepository->externalId);

                return $storedRepository?->package_json_oid !== $collectedRepository->packageJsonVersion;
            },
        );

        return Repository::query()
            ->ofSource($sourceKey)
            ->ofGitHubIds($collectedRepositoriesWithChangedPackageJson->externalIds())
            ->get();
    }
}
