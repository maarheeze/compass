<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Sites;

use Generator;
use Maarheeze\Compass\Collections\CollectedSiteCollection;
use Maarheeze\Compass\Collector\CollectingFailed;
use Maarheeze\Compass\Collector\RequestBudgetExhausted;

interface SitesCollector
{
    /**
     * @return Generator<int, CollectedSiteCollection>
     *
     * @throws CollectingFailed
     * @throws RequestBudgetExhausted
     */
    public function sites(): Generator;
}
