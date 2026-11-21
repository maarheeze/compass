<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collections;

use Illuminate\Contracts\Translation\Translator;
use Illuminate\Database\Eloquent\Collection;
use Maarheeze\Compass\Collector\Issues\IssueType;
use Maarheeze\Compass\Http\Assignee;
use Maarheeze\Compass\Http\AssigneeFilter;
use Maarheeze\Compass\Http\IssueTypeFilter;
use Maarheeze\Compass\Models\Issue;
use Maarheeze\Compass\View\FilterOption;
use Webmozart\Assert\Assert;

/**
 * @extends Collection<array-key, Issue>
 */
class IssueCollection extends Collection
{
    public function assigneeFilterOptions(Translator $translator): FilterOptionCollection
    {
        $assigneeFilterOptions = new FilterOptionCollection();

        foreach ($this as $issue) {
            foreach ($issue->assignees as $issueAssignee) {
                $assignee = new Assignee($issue->source, $issueAssignee->login);
                $assigneeFilterOptions->countOption($issueAssignee->login, $assignee->routeParameters(), $issueAssignee->avatar_url);
            }
        }

        return $assigneeFilterOptions
            ->sortedByLabel()
            ->prepend(new FilterOption(
                Assert::string($translator->get('compass::assignee.unassigned')),
                $this->unassigned()->count(),
                AssigneeFilter::unassignedRouteParameters(),
                null,
            ));
    }

    public function issueTypeFilterOptions(Translator $translator): FilterOptionCollection
    {
        $issueTypeFilterOptions = new FilterOptionCollection();

        foreach (IssueType::displayOrder() as $issueType) {
            $issueTypeFilterOptions->push(new FilterOption(
                Assert::string($translator->get($issueType->labelKey())),
                $this->ofIssueType($issueType)->count(),
                IssueTypeFilter::issueTypeRouteParameters($issueType),
                null,
            ));
        }

        return $issueTypeFilterOptions->push(new FilterOption(
            Assert::string($translator->get('compass::issue.untyped')),
            $this->untyped()->count(),
            IssueTypeFilter::untypedRouteParameters(),
            null,
        ));
    }

    public function ofIssueType(IssueType $issueType): self
    {
        return $this->filter(static function (Issue $issue) use ($issueType): bool {
            return $issue->type === $issueType;
        });
    }

    public function unassigned(): self
    {
        return $this->filter(static function (Issue $issue): bool {
            return $issue->assignees->isEmpty();
        });
    }

    public function untyped(): self
    {
        return $this->filter(static function (Issue $issue): bool {
            return $issue->type === null;
        });
    }
}
