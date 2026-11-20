<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Models\Builders;

use Illuminate\Database\Eloquent\Builder;
use Maarheeze\Compass\Collections\SiteCollection;
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
        return Assert::isInstanceOf(
            $this
                ->with(['reports', 'repository'])
                ->orderBy('name')
                ->get(),
            SiteCollection::class,
        );
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

    public function ofRepositorySource(string $source): self
    {
        return $this->whereHas('repository', static function (RepositoryBuilder $repositoryQuery) use ($source): void {
            $repositoryQuery->ofSource($source);
        });
    }

    public function ofSource(string $source): self
    {
        return $this->where('source', $source);
    }

    public function returnedByRun(Uuid $runId): self
    {
        return $this->where('run_id', $runId);
    }
}
