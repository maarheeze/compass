<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\SiteDomains;

readonly class CollectedSiteDomain
{
    public function __construct(
        public string $name,
        public SiteDomainType $type,
        public SiteDomainStatus $status,
    ) {
    }
}
