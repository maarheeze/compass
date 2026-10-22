<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Analyzer\GitHub;

use Maarheeze\Compass\Models\PullRequest;
use Maarheeze\Compass\Models\User;

readonly class PullRequestCounter
{
    public function count(User $user): PullRequestCounts
    {
        return new PullRequestCounts(
            PullRequest::query()->count(),
            PullRequest::query()->assignedTo($user)->count(),
        );
    }
}
