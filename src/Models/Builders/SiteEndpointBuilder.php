<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Models\Builders;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Maarheeze\Compass\Collector\SiteEndpoint\EndpointPath;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\Models\SiteEndpoint;

/**
 * @extends Builder<SiteEndpoint>
 */
class SiteEndpointBuilder extends Builder
{
    public function dueForCheck(CarbonImmutable $now): self
    {
        return $this
            ->ofCollectedSites()
            ->where('due_at', '<=', $now);
    }

    public function ofCollectedSites(): self
    {
        return $this->whereHas('siteReport.site', static function (SiteBuilder $siteQuery): void {
            $siteQuery->collected();
        });
    }

    public function ofPath(EndpointPath $endpointPath): self
    {
        return $this->where('path', $endpointPath);
    }

    public function ofSite(Site $site): self
    {
        return $this->whereRelation('siteReport', 'site_id', $site->getKey());
    }
}
