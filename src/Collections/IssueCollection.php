<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collections;

use Illuminate\Contracts\Translation\Translator;
use Illuminate\Database\Eloquent\Collection;
use Maarheeze\Compass\Http\Assignee;
use Maarheeze\Compass\Http\AssigneeFilter;
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

    public function unassigned(): self
    {
        return $this->filter(static function (Issue $issue): bool {
            return $issue->assignees->isEmpty();
        });
    }
}
