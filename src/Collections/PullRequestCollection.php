<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collections;

use Illuminate\Contracts\Translation\Translator;
use Illuminate\Database\Eloquent\Collection;
use Maarheeze\Compass\Http\Assignee;
use Maarheeze\Compass\Http\AssigneeFilter;
use Maarheeze\Compass\Models\PullRequest;
use Maarheeze\Compass\View\FilterOption;
use Webmozart\Assert\Assert;

/**
 * @extends Collection<array-key, PullRequest>
 */
class PullRequestCollection extends Collection
{
    public function assigneeFilterOptions(Translator $translator): FilterOptionCollection
    {
        $assigneeFilterOptions = new FilterOptionCollection();

        foreach ($this as $pullRequest) {
            foreach ($pullRequest->assignees as $pullRequestAssignee) {
                $assignee = new Assignee($pullRequest->source, $pullRequestAssignee->login);
                $assigneeFilterOptions->countOption($pullRequestAssignee->login, $assignee->routeParameters(), $pullRequestAssignee->avatar_url);
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
        return $this->filter(static function (PullRequest $pullRequest): bool {
            return $pullRequest->assignees->isEmpty();
        });
    }
}
