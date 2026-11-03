<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Issues;

use Generator;
use Maarheeze\Compass\Collections\CollectedIssueCollection;
use Maarheeze\Compass\Collector\CollectingFailed;

interface IssuesCollector
{
    /**
     * @return Generator<int, CollectedIssueCollection>
     *
     * @throws CollectingFailed
     */
    public function issues(): Generator;
}
