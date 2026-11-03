<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Forge;

use Maarheeze\Compass\Collections\CollectedSiteCollection;

readonly class SitesQueryResult
{
    public function __construct(
        public CollectedSiteCollection $collectedSites,
        public ?string $nextCursor,
    ) {
    }
}
