<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collections;

use Illuminate\Support\Collection;
use Maarheeze\Compass\Collector\SiteDomains\CollectedSiteDomain;

/**
 * @extends Collection<int, CollectedSiteDomain>
 */
class CollectedSiteDomainCollection extends Collection
{
    /**
     * @return Collection<int, string>
     */
    public function names(): Collection
    {
        return $this->toBase()->map(static function (CollectedSiteDomain $collectedSiteDomain): string {
            return $collectedSiteDomain->name;
        });
    }
}
