<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collections;

use Illuminate\Database\Eloquent\Collection;
use Maarheeze\Compass\Collector\SiteEndpoint\SiteEndpointState;
use Maarheeze\Compass\Models\SiteEndpoint;

/**
 * @extends Collection<array-key, SiteEndpoint>
 */
class SiteEndpointCollection extends Collection
{
    public function down(): self
    {
        return $this->filter(static function (SiteEndpoint $siteEndpoint): bool {
            return $siteEndpoint->latestCheck?->state === SiteEndpointState::DOWN;
        });
    }

    public function notDown(): self
    {
        return $this->filter(static function (SiteEndpoint $siteEndpoint): bool {
            return $siteEndpoint->latestCheck?->state !== SiteEndpointState::DOWN;
        });
    }

    public function up(): self
    {
        return $this->filter(static function (SiteEndpoint $siteEndpoint): bool {
            return $siteEndpoint->latestCheck?->state === SiteEndpointState::UP;
        });
    }
}
