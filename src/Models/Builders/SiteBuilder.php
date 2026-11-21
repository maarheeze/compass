<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Models\Builders;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Maarheeze\Compass\Collections\SiteCollection;
use Maarheeze\Compass\Http\ItemsPerPage;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Uuid\Uuid;
use Webmozart\Assert\Assert;

/**
 * @extends Builder<Site>
 */
class SiteBuilder extends Builder
{
    public function collected(): self
    {
        return $this->whereNull('missing_at');
    }

    public function listed(): SiteCollection
    {
        return Assert::isInstanceOf($this->inListedOrder()->get(), SiteCollection::class);
    }

    /**
     * @return LengthAwarePaginator<int, Site>
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

    public function ofPhpVersion(string $phpVersion): self
    {
        return $this->where('php_version', $phpVersion);
    }

    public function ofRepositorySource(string $source): self
    {
        return $this->whereHas('repository', static function (RepositoryBuilder $repositoryQuery) use ($source): void {
            $repositoryQuery->ofSource($source);
        });
    }

    public function ofServer(string $serverName): self
    {
        return $this->where('server_name', $serverName);
    }

    public function ofSource(string $source): self
    {
        return $this->where('source', $source);
    }

    public function returnedByRun(Uuid $runId): self
    {
        return $this->where('run_id', $runId);
    }

    private function inListedOrder(): self
    {
        return $this
            ->with(['reports', 'repository'])
            ->orderBy('name');
    }
}
