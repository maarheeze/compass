<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Forge;

use Maarheeze\Compass\Collections\ForgeSiteCollection;

readonly class SitesQueryResult
{
    public function __construct(
        public ForgeSiteCollection $forgeSites,
        public ?string $nextCursor,
    ) {
    }
}
