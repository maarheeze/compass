<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Models\Builders;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Builder;
use Maarheeze\Compass\Alerts\AlertStatus;
use Maarheeze\Compass\Models\Alert;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\Models\SiteEndpoint;

/**
 * @extends Builder<Alert>
 */
class AlertBuilder extends Builder
{
    public function muted(CarbonImmutable $now): self
    {
        return $this->where('muted_until', '>', $now);
    }

    public function notDone(): self
    {
        return $this->whereNot('status', AlertStatus::DONE);
    }

    public function notMuted(CarbonImmutable $now): self
    {
        return $this->where(static function (self $alertQuery) use ($now): void {
            $alertQuery
                ->whereNull('muted_until')
                ->orWhere('muted_until', '<=', $now);
        });
    }

    public function ofSite(Site $site): self
    {
        return $this->where(static function (self $alertQuery) use ($site): void {
            $alertQuery
                ->whereMorphedTo('subject', $site)
                ->orWhereHasMorph(
                    'subject',
                    [SiteEndpoint::class],
                    static function (QueryBuilder $siteEndpointQuery) use ($site): void {
                        $siteEndpointQuery->where('site_id', $site->id);
                    },
                );
        });
    }
}
