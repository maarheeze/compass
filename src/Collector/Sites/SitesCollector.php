<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Sites;

use Generator;
use Maarheeze\Compass\Collections\CollectedSiteCollection;
use Maarheeze\Compass\Collector\CollectingFailed;

interface SitesCollector
{
    /**
     * @return Generator<int, CollectedSiteCollection>
     *
     * @throws CollectingFailed
     */
    public function sites(): Generator;
}
