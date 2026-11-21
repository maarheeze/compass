<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Models\Builders;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Maarheeze\Compass\Collections\IssueCollection;
use Maarheeze\Compass\Collector\Issues\IssueType;
use Maarheeze\Compass\Http\Assignee;
use Maarheeze\Compass\Http\IssueTypeOrder;
use Maarheeze\Compass\Http\ItemsPerPage;
use Maarheeze\Compass\Models\Issue;
use Maarheeze\Uuid\Uuid;
use Webmozart\Assert\Assert;

/**
 * @extends Builder<Issue>
 */
class IssueBuilder extends Builder
{
    public function assignedTo(Assignee $assignee): self
    {
        return $this
            ->ofSource($assignee->source)
            ->whereHas('assignees', static function (IssueAssigneeBuilder $issueAssignees) use ($assignee): void {
                $issueAssignees->where('login', $assignee->login);
            });
    }

    public function latestOpened(): self
    {
        return $this->latest('opened_at');
    }

    public function listed(IssueTypeOrder $issueTypeOrder): IssueCollection
    {
        return Assert::isInstanceOf($this->inListedOrder($issueTypeOrder)->get(), IssueCollection::class);
    }

    /**
     * @return LengthAwarePaginator<int, Issue>
     */
    public function listedPage(ItemsPerPage $itemsPerPage, IssueTypeOrder $issueTypeOrder): LengthAwarePaginator
    {
        return $this
            ->inListedOrder($issueTypeOrder)
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

    public function ofIssueType(IssueType $issueType): self
    {
        return $this->where('type', $issueType);
    }

    public function ofSource(string $source): self
    {
        return $this->where('source', $source);
    }

    public function unassigned(): self
    {
        return $this->whereDoesntHave('assignees');
    }

    public function untyped(): self
    {
        return $this->whereNull('type');
    }

    private function inListedOrder(IssueTypeOrder $issueTypeOrder): self
    {
        return $this
            ->with(['assignees', 'repository'])
            ->inOrderOf('type', $issueTypeOrder->issueTypes)
            ->latestOpened();
    }
}
