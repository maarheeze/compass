<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Models\Builders;

use Illuminate\Database\Eloquent\Builder;
use Maarheeze\Compass\Models\SiteEndpoint;

/**
 * @extends Builder<SiteEndpoint>
 */
class SiteEndpointBuilder extends Builder
{
    public function ofCollectedSites(): self
    {
        return $this->whereHas('site', static function (SiteBuilder $siteQuery): void {
            $siteQuery->collected();
        });
    }
}
