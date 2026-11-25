<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Issues;

use Generator;
use Maarheeze\Compass\Collections\CollectedIssueCollection;
use Maarheeze\Compass\Collector\CollectingFailed;
use Maarheeze\Compass\Collector\RequestBudgetExhausted;

interface IssuesCollector
{
    /**
     * @return Generator<int, CollectedIssueCollection>
     *
     * @throws CollectingFailed
     * @throws RequestBudgetExhausted
     */
    public function issues(): Generator;
}
