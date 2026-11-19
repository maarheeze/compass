<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Models\Builders;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Maarheeze\Compass\Collections\IssueCollection;
use Maarheeze\Compass\Models\Issue;
use Maarheeze\Uuid\Uuid;
use Webmozart\Assert\Assert;

/**
 * @extends Builder<Issue>
 */
class IssueBuilder extends Builder
{
    public function latestOpened(): self
    {
        return $this->latest('opened_at');
    }

    public function listed(): IssueCollection
    {
        return Assert::isInstanceOf(
            $this
                ->with(['assignees', 'repository'])
                ->latestOpened()
                ->get(),
            IssueCollection::class,
        );
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

    public function ofSource(string $source): self
    {
        return $this->where('source', $source);
    }
}
