<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Repositories;

use Carbon\CarbonImmutable;
use Maarheeze\Compass\Collections\CollectedRepositoryCollection;
use Maarheeze\Compass\Models\Repository;
use Maarheeze\Uuid\Uuid;
use Psr\Clock\ClockInterface;

readonly class RepositorySynchronizer
{
    public function __construct(
        private ClockInterface $clock,
    ) {
    }

    public function collectedRepositoriesWithChangedPackageJson(
        CollectedRepositoryCollection $collectedRepositories,
        string $source,
    ): CollectedRepositoryCollection {
        $storedRepositories = Repository::query()
            ->ofSource($source)
            ->ofExternalIds($collectedRepositories->externalIds())
            ->get();

        return $collectedRepositories->filter(
            static function (CollectedRepository $collectedRepository) use ($storedRepositories): bool {
                $storedRepository = $storedRepositories->firstWhere('external_id', $collectedRepository->externalId);

                return $storedRepository?->package_json_version !== $collectedRepository->packageJsonVersion;
            },
        );
    }

    public function markUncollectedMissing(string $source, Uuid $runId): void
    {
        $missingAt = CarbonImmutable::instance($this->clock->now());

        Repository::query()
            ->ofSource($source)
            ->notReturnedByRun($runId)
            ->collected()
            ->update(['missing_at' => $missingAt]);
    }

    public function storeRepositories(
        CollectedRepositoryCollection $collectedRepositories,
        string $source,
        Uuid $runId,
    ): void {
        $repositoryValues = [];
        $collectedAt = CarbonImmutable::instance($this->clock->now());

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
                'collected_at' => $collectedAt,
                'missing_at' => null,
                'missing_ignored_at' => null,
            ];
        }

        Repository::query()->upsert($repositoryValues, ['source', 'external_id']);
    }
}
