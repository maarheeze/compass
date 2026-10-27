<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Analyzer\SiteEndpoint;

use Illuminate\Database\Eloquent\Builder;
use Maarheeze\Compass\Analyzer\AlertDetector;
use Maarheeze\Compass\Collections\AlertCollection;
use Maarheeze\Compass\Collections\SiteEndpointCheckCollection;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\Models\SiteEndpoint;
use Maarheeze\Compass\Models\SiteEndpointCheck;
use Webmozart\Assert\Assert;

readonly class DownSiteEndpointAlertDetector implements AlertDetector
{
    public function findAlerts(): AlertCollection
    {
        $configuredSiteEndpointQuery = SiteEndpoint::query()
            ->whereColumn('compass_site_endpoints.site_id', 'compass_site_endpoint_checks.site_id')
            ->whereColumn('compass_site_endpoints.path', 'compass_site_endpoint_checks.path');

        $downSiteEndpointChecks = Assert::isInstanceOf(
            SiteEndpointCheck::query()
                ->latestPerPath()
                ->down()
                ->whereExists($configuredSiteEndpointQuery)
                ->whereHas('site', static function (Builder $siteQuery): void {
                    $siteQuery->whereNull('missing_at');
                })
                ->with('site')
                ->orderBy(Site::query()->select('name')->whereColumn('compass_sites.id', 'compass_site_endpoint_checks.site_id'))
                ->orderBy('path')
                ->get(),
            SiteEndpointCheckCollection::class,
        );

        return new AlertCollection(
            $downSiteEndpointChecks
                ->toBase()
                ->mapInto(DownSiteEndpointAlert::class)
                ->values(),
        );
    }
}
