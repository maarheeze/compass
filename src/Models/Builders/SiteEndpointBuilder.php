<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Models\Builders;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Maarheeze\Compass\Collector\SiteEndpoint\EndpointPath;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\Models\SiteEndpoint;
use Maarheeze\Compass\Models\SiteEndpointCheck;

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

    public function withLatestCheck(): self
    {
        return $this
            ->addSelect(['latest_check_id' => $this->latestCheckIdOfEndpoint()])
            ->with(['latestCheck' => static function (BelongsTo $latestCheckQuery): void {
                $latestCheckQuery->withOnlineSince();
            },
            ]);
    }

    /**
     * @return Builder<SiteEndpointCheck>
     */
    private function latestCheckIdOfEndpoint(): Builder
    {
        return SiteEndpointCheck::query()
            ->select('compass_site_endpoint_checks.id')
            ->whereHas('site.reports', static function (QueryBuilder $siteReportQuery): void {
                $siteReportQuery->whereColumn('compass_site_reports.id', 'compass_site_endpoints.site_report_id');
            })
            ->whereColumn('compass_site_endpoint_checks.path', 'compass_site_endpoints.path')
            ->latest('compass_site_endpoint_checks.id')
            ->limit(1);
    }
}
