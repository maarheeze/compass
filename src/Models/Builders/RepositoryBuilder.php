<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Models\Builders;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Maarheeze\Compass\Models\Repository;
use Maarheeze\Uuid\Uuid;

use function sprintf;

/**
 * @extends Builder<Repository>
 */
class RepositoryBuilder extends Builder
{
    public function collected(): self
    {
        return $this->whereNull('missing_at');
    }

    public function composerJsonOutOfDate(): self
    {
        return $this->storedFileOutOfDate('composer_json_oid', 'composerJson');
    }

    public function composerLockOutOfDate(): self
    {
        return $this->storedFileOutOfDate('composer_lock_oid', 'composerLock');
    }

    public function dependabotYmlOutOfDate(): self
    {
        return $this->storedFileOutOfDate('dependabot_yml_oid', 'dependabotYml');
    }

    public function missing(): self
    {
        return $this
            ->whereNotNull('missing_at')
            ->whereNull('missing_ignored_at');
    }

    /**
     * @param Collection<int, string> $names
     */
    public function named(Collection $names): self
    {
        return $this->whereIn('name', $names);
    }

    public function notCollected(): self
    {
        return $this->whereNotNull('missing_at');
    }

    public function notReturnedByRun(Uuid $runId): self
    {
        return $this->whereNot('run_id', $runId);
    }

    /**
     * @param Collection<int, string> $gitHubIds
     */
    public function ofGitHubIds(Collection $gitHubIds): self
    {
        return $this->whereIn('github_id', $gitHubIds);
    }

    public function ofSource(string $source): self
    {
        return $this->where('source', $source);
    }

    public function returnedByRun(Uuid $runId): self
    {
        return $this->where('run_id', $runId);
    }

    private function storedFileOutOfDate(string $oidColumn, string $storedFileRelation): self
    {
        return $this->where(static function (self $repositoryQuery) use ($oidColumn, $storedFileRelation): void {
            $repositoryQuery
                ->where(static function (self $changedFileQuery) use ($oidColumn, $storedFileRelation): void {
                    $changedFileQuery
                        ->whereNotNull($oidColumn)
                        ->whereDoesntHave(
                            $storedFileRelation,
                            static function (Builder $storedFileQuery) use ($oidColumn): void {
                                $storedFileQuery->whereColumn(
                                    'oid',
                                    sprintf('compass_repositories.%s', $oidColumn),
                                );
                            },
                        );
                })
                ->orWhere(static function (self $removedFileQuery) use ($oidColumn, $storedFileRelation): void {
                    $removedFileQuery
                        ->whereNull($oidColumn)
                        ->has($storedFileRelation);
                });
        });
    }
}
