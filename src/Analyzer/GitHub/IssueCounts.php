<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Analyzer\GitHub;

readonly class IssueCounts
{
    public function __construct(
        public int $openIssueCount,
        public int $assignedIssueCount,
    ) {
    }
}
