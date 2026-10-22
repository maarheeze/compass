<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Analyzer\GitHub;

use Maarheeze\Compass\Models\Issue;
use Maarheeze\Compass\Models\User;

readonly class IssueCounter
{
    public function count(User $user): IssueCounts
    {
        return new IssueCounts(
            Issue::query()->count(),
            Issue::query()->assignedTo($user)->count(),
        );
    }
}
