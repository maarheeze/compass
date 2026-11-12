<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Models\Builders;

use Illuminate\Contracts\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Builder;
use Maarheeze\Compass\Collector\SiteEndpoint\EndpointPath;
use Maarheeze\Compass\Models\SiteEndpoint;
use Maarheeze\Compass\Models\SiteEndpointCheck;

/**
 * @extends Builder<SiteEndpointCheck>
 */
class SiteEndpointCheckBuilder extends Builder
{
    public function down(): self
    {
        return $this->where('is_successful', false);
    }

    public function latestPerPath(): self
    {
        return $this->whereNotExists(
            SiteEndpointCheck::query()
                ->from('compass_site_endpoint_checks', 'newer_site_endpoint_checks')
                ->whereColumn('newer_site_endpoint_checks.site_id', 'compass_site_endpoint_checks.site_id')
                ->whereColumn('newer_site_endpoint_checks.path', 'compass_site_endpoint_checks.path')
                ->whereColumn('newer_site_endpoint_checks.id', '>', 'compass_site_endpoint_checks.id'),
        );
    }

    public function notReported(): self
    {
        return $this->whereNotExists($this->siteEndpointsOfCheck());
    }

    public function ofPath(EndpointPath $endpointPath): self
    {
        return $this->where('path', $endpointPath);
    }

    public function reported(): self
    {
        return $this->whereExists($this->siteEndpointsOfCheck());
    }

    public function withOnlineSince(): self
    {
        return $this
            ->addSelect(['online_since' => $this->onlineSinceOfCheck()])
            ->withCasts(['online_since' => 'immutable_datetime']);
    }

    /**
     * @return Builder<SiteEndpointCheck>
     */
    private function failedChecksAfterSuccessfulCheck(): Builder
    {
        return SiteEndpointCheck::query()
            ->from('compass_site_endpoint_checks', 'failed_site_endpoint_checks')
            ->whereColumn('failed_site_endpoint_checks.site_id', 'successful_site_endpoint_checks.site_id')
            ->whereColumn('failed_site_endpoint_checks.path', 'successful_site_endpoint_checks.path')
            ->where('failed_site_endpoint_checks.is_successful', false)
            ->whereColumn('failed_site_endpoint_checks.id', '>', 'successful_site_endpoint_checks.id');
    }

    /**
     * @return Builder<SiteEndpointCheck>
     */
    private function onlineSinceOfCheck(): Builder
    {
        return SiteEndpointCheck::query()
            ->from('compass_site_endpoint_checks', 'successful_site_endpoint_checks')
            ->select('successful_site_endpoint_checks.created_at')
            ->whereColumn('successful_site_endpoint_checks.site_id', 'compass_site_endpoint_checks.site_id')
            ->whereColumn('successful_site_endpoint_checks.path', 'compass_site_endpoint_checks.path')
            ->where('successful_site_endpoint_checks.is_successful', true)
            ->whereNotExists($this->failedChecksAfterSuccessfulCheck())
            ->orderBy('successful_site_endpoint_checks.id')
            ->limit(1);
    }

    /**
     * @return Builder<SiteEndpoint>
     */
    private function siteEndpointsOfCheck(): Builder
    {
        return SiteEndpoint::query()
            ->whereHas('siteReport', static function (QueryBuilder $siteReportQuery): void {
                $siteReportQuery->whereColumn('compass_site_reports.site_id', 'compass_site_endpoint_checks.site_id');
            })
            ->whereColumn('compass_site_endpoints.path', 'compass_site_endpoint_checks.path');
    }
}
