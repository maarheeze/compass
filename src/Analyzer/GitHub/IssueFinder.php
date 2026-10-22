<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Analyzer\GitHub;

use Maarheeze\Compass\Collections\IssueCollection;
use Maarheeze\Compass\Models\Issue;
use Maarheeze\Compass\Models\User;
use Webmozart\Assert\Assert;

readonly class IssueFinder
{
    public function findIssues(): IssueCollection
    {
        return Assert::isInstanceOf(
            Issue::query()
                ->with(['assignees', 'repository'])
                ->latest('opened_at')
                ->get(),
            IssueCollection::class,
        );
    }

    public function findIssuesAssignedTo(User $user): IssueCollection
    {
        return Assert::isInstanceOf(
            Issue::query()
                ->with(['assignees', 'repository'])
                ->assignedTo($user)
                ->latest('opened_at')
                ->get(),
            IssueCollection::class,
        );
    }
}
