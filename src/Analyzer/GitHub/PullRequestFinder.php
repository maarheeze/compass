<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Analyzer\GitHub;

use Maarheeze\Compass\Collections\PullRequestCollection;
use Maarheeze\Compass\Models\PullRequest;
use Maarheeze\Compass\Models\User;
use Webmozart\Assert\Assert;

readonly class PullRequestFinder
{
    public function findPullRequests(): PullRequestCollection
    {
        return Assert::isInstanceOf(
            PullRequest::query()
                ->with(['assignees', 'repository'])
                ->latest('opened_at')
                ->get(),
            PullRequestCollection::class,
        );
    }

    public function findPullRequestsAssignedTo(User $user): PullRequestCollection
    {
        return Assert::isInstanceOf(
            PullRequest::query()
                ->with(['assignees', 'repository'])
                ->assignedTo($user)
                ->latest('opened_at')
                ->get(),
            PullRequestCollection::class,
        );
    }
}
