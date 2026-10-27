<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Models\Builders;

use Illuminate\Database\Eloquent\Builder;
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
}
