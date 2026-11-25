<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\PullRequests;

use Generator;
use Maarheeze\Compass\Collections\CollectedPullRequestCollection;
use Maarheeze\Compass\Collector\CollectingFailed;
use Maarheeze\Compass\Collector\RequestBudgetExhausted;

interface PullRequestsCollector
{
    /**
     * @return Generator<int, CollectedPullRequestCollection>
     *
     * @throws CollectingFailed
     * @throws RequestBudgetExhausted
     */
    public function pullRequests(): Generator;
}
