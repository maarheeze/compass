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
    public function markUncollectedMissing(string $source, Uuid $runId): void
    {
        Repository::query()
            ->ofSource($source)
            ->notReturnedByRun($runId)
            ->collected()
            ->update(['missing_at' => CarbonImmutable::now()]);
    }

    public function storeRepositories(
        CollectedRepositoryCollection $collectedRepositories,
        string $source,
        Uuid $runId,
    ): RepositoryCollection {
        $storedRepositories = Repository::query()
            ->ofSource($source)
            ->ofExternalIds($collectedRepositories->externalIds())
            ->get();
        $repositoryValues = [];

        foreach ($collectedRepositories as $collectedRepository) {
            $repositoryValues[] = [
                'source' => $source,
                'external_id' => $collectedRepository->externalId,
                'name' => $collectedRepository->name,
                'full_name' => $collectedRepository->fullName,
                'url' => $collectedRepository->url,
                'default_branch' => $collectedRepository->defaultBranch,
                'last_commit_sha' => $collectedRepository->lastCommitSha,
                'last_commit_committed_at' => $collectedRepository->lastCommitCommittedAt,
                'visibility' => $collectedRepository->visibility,
                'composer_json_version' => $collectedRepository->composerJsonVersion,
                'composer_lock_version' => $collectedRepository->composerLockVersion,
                'package_json_version' => $collectedRepository->packageJsonVersion,
                'dependabot_yml_version' => $collectedRepository->dependabotYmlVersion,
                'run_id' => $runId,
                'collected_at' => CarbonImmutable::now(),
                'missing_at' => null,
                'missing_ignored_at' => null,
            ];
        }

        Repository::query()->upsert($repositoryValues, ['source', 'external_id']);

        return $this->repositoriesWithChangedPackageJson($collectedRepositories, $source, $storedRepositories);
    }

    private function repositoriesWithChangedPackageJson(
        CollectedRepositoryCollection $collectedRepositories,
        string $source,
        RepositoryCollection $storedRepositories,
    ): RepositoryCollection {
        $collectedRepositoriesWithChangedPackageJson = $collectedRepositories->filter(
            static function (CollectedRepository $collectedRepository) use ($storedRepositories): bool {
                $storedRepository = $storedRepositories->firstWhere('external_id', $collectedRepository->externalId);

                return $storedRepository?->package_json_version !== $collectedRepository->packageJsonVersion;
            },
        );

        return Repository::query()
            ->ofSource($source)
            ->ofExternalIds($collectedRepositoriesWithChangedPackageJson->externalIds())
            ->get();
    }
}
