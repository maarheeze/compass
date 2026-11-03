<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\PullRequests;

use Generator;
use Maarheeze\Compass\Collections\CollectedPullRequestCollection;
use Maarheeze\Compass\Collector\CollectingFailed;

interface PullRequestsCollector
{
    /**
     * @return Generator<int, CollectedPullRequestCollection>
     *
     * @throws CollectingFailed
     */
    public function pullRequests(): Generator;
}
