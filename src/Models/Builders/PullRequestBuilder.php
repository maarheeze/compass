<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Models\Builders;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Maarheeze\Compass\Collections\PullRequestCollection;
use Maarheeze\Compass\Http\Assignee;
use Maarheeze\Compass\Http\ItemsPerPage;
use Maarheeze\Compass\Models\PullRequest;
use Maarheeze\Uuid\Uuid;
use Webmozart\Assert\Assert;

/**
 * @extends Builder<PullRequest>
 */
class PullRequestBuilder extends Builder
{
    public function assignedTo(Assignee $assignee): self
    {
        return $this
            ->ofSource($assignee->source)
            ->whereHas('assignees', static function (PullRequestAssigneeBuilder $pullRequestAssignees) use ($assignee): void {
                $pullRequestAssignees->where('login', $assignee->login);
            });
    }

    public function latestOpened(): self
    {
        return $this->latest('opened_at');
    }

    public function listed(): PullRequestCollection
    {
        return Assert::isInstanceOf($this->inListedOrder()->get(), PullRequestCollection::class);
    }

    /**
     * @return LengthAwarePaginator<int, PullRequest>
     */
    public function listedPage(ItemsPerPage $itemsPerPage): LengthAwarePaginator
    {
        return $this
            ->inListedOrder()
            ->paginate($itemsPerPage->value)
            ->withQueryString();
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

    public function unassigned(): self
    {
        return $this->whereDoesntHave('assignees');
    }

    private function inListedOrder(): self
    {
        return $this
            ->with(['assignees', 'repository'])
            ->latestOpened();
    }
}
