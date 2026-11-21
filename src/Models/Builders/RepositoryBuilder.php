<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Models\Builders;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Maarheeze\Compass\Analyser\Composer\RepositoryKind;
use Maarheeze\Compass\Collections\RepositoryCollection;
use Maarheeze\Compass\Collector\Composer\ComposerType;
use Maarheeze\Compass\Collector\Repositories\RepositoryVisibility;
use Maarheeze\Compass\Http\ItemsPerPage;
use Maarheeze\Compass\Models\Repository;
use Maarheeze\Uuid\Uuid;
use Webmozart\Assert\Assert;

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
        return $this->storedFileOutOfDate('composer_json_version', 'composerJson');
    }

    public function composerLockOutOfDate(): self
    {
        return $this->storedFileOutOfDate('composer_lock_version', 'composerLock');
    }

    public function dependabotYmlOutOfDate(): self
    {
        return $this->storedFileOutOfDate('dependabot_yml_version', 'dependabotYml');
    }

    public function listed(): RepositoryCollection
    {
        return Assert::isInstanceOf($this->inListedOrder()->get(), RepositoryCollection::class);
    }

    /**
     * @return LengthAwarePaginator<int, Repository>
     */
    public function listedPage(ItemsPerPage $itemsPerPage): LengthAwarePaginator
    {
        return $this
            ->inListedOrder()
            ->paginate($itemsPerPage->value)
            ->withQueryString();
    }

    public function missing(): self
    {
        return $this
            ->whereNotNull('missing_at')
            ->whereNull('missing_ignored_at');
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
     * @param Collection<int, string> $externalIds
     */
    public function ofExternalIds(Collection $externalIds): self
    {
        return $this->whereIn('external_id', $externalIds);
    }

    /**
     * @param Collection<int, string> $fullNames
     */
    public function ofFullNames(Collection $fullNames): self
    {
        return $this->whereIn('full_name', $fullNames);
    }

    public function ofKind(RepositoryKind $kind): self
    {
        $composerType = $kind->composerType();

        if ($composerType === null) {
            return $this->whereNot(static function (self $repositoryOfComposerTypeQuery): void {
                $repositoryOfComposerTypeQuery->ofComposerTypes(ComposerType::cases());
            });
        }

        return $this->ofComposerTypes([$composerType]);
    }

    public function ofSource(string $source): self
    {
        return $this->where('source', $source);
    }

    public function ofVisibility(RepositoryVisibility $visibility): self
    {
        return $this->where('visibility', $visibility);
    }

    public function returnedByRun(Uuid $runId): self
    {
        return $this->where('run_id', $runId);
    }

    private function inListedOrder(): self
    {
        return $this
            ->with('composerJson')
            ->withCount(['issues', 'pullRequests'])
            ->orderBy('name');
    }

    /**
     * @param list<ComposerType> $composerTypes
     */
    private function ofComposerTypes(array $composerTypes): self
    {
        return $this
            ->whereNotNull('composer_json_version')
            ->whereHas('composerJson', static function (Builder $composerJsonQuery) use ($composerTypes): void {
                $composerJsonQuery->whereIn('type', $composerTypes);
            });
    }

    private function storedFileOutOfDate(string $versionColumn, string $storedFileRelation): self
    {
        return $this->where(static function (self $repositoryQuery) use ($versionColumn, $storedFileRelation): void {
            $repositoryQuery
                ->where(static function (self $changedFileQuery) use ($versionColumn, $storedFileRelation): void {
                    $changedFileQuery
                        ->whereNotNull($versionColumn)
                        ->whereDoesntHave(
                            $storedFileRelation,
                            static function (Builder $storedFileQuery) use ($versionColumn): void {
                                $storedFileQuery->whereColumn(
                                    'version',
                                    sprintf('compass_repositories.%s', $versionColumn),
                                );
                            },
                        );
                })
                ->orWhere(static function (self $removedFileQuery) use ($versionColumn, $storedFileRelation): void {
                    $removedFileQuery
                        ->whereNull($versionColumn)
                        ->has($storedFileRelation);
                });
        });
    }
}
