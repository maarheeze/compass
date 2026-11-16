<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collections;

use Illuminate\Support\Collection;
use Maarheeze\Compass\Collector\Sites\CollectedSite;

use function preg_match;

/**
 * @extends Collection<int, CollectedSite>
 */
class CollectedSiteCollection extends Collection
{
    public function onServersMatching(string $serverPattern): self
    {
        $collectedSitesOnServersMatching = $this->filter(
            static function (CollectedSite $collectedSite) use ($serverPattern): bool {
                return preg_match($serverPattern, $collectedSite->serverName) === 1;
            },
        );

        return $collectedSitesOnServersMatching->values();
    }
}
