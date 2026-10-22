<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Analyzer\GitHub;

readonly class PullRequestCounts
{
    public function __construct(
        public int $openPullRequestCount,
        public int $assignedPullRequestCount,
    ) {
    }
}
