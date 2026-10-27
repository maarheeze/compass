<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\GitHub;

use Carbon\CarbonImmutable;
use Maarheeze\Compass\Collections\GitHubRepositoryCollection;
use Maarheeze\Compass\Collector\RunId;
use Maarheeze\Compass\Models\Repository;

readonly class RepositorySynchronizer
{
    public function markUncollectedMissing(RunId $runId): void
    {
        Repository::query()
            ->whereNot('run_id', $runId)
            ->whereNull('missing_at')
            ->update(['missing_at' => CarbonImmutable::now()]);
    }

    public function storeRepositories(
        GitHubRepositoryCollection $gitHubRepositories,
        RunId $runId,
    ): void {
        $repositoryValues = [];

        foreach ($gitHubRepositories as $gitHubRepository) {
            $repositoryValues[] = [
                'github_id' => $gitHubRepository->gitHubId,
                'name' => $gitHubRepository->name,
                'url' => $gitHubRepository->url,
                'default_branch' => $gitHubRepository->defaultBranch,
                'last_commit_sha' => $gitHubRepository->lastCommitSha,
                'last_commit_committed_at' => $gitHubRepository->lastCommitCommittedAt,
                'visibility' => $gitHubRepository->visibility,
                'composer_json_oid' => $gitHubRepository->composerJsonOid,
                'composer_lock_oid' => $gitHubRepository->composerLockOid,
                'run_id' => $runId,
                'collected_at' => CarbonImmutable::now(),
                'missing_at' => null,
                'missing_ignored_at' => null,
            ];
        }

        Repository::query()->upsert($repositoryValues, ['github_id']);
    }
}
