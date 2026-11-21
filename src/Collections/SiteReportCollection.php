<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collections;

use Illuminate\Database\Eloquent\Collection;
use Maarheeze\Compass\Models\SiteReport;

/**
 * @extends Collection<array-key, SiteReport>
 */
class SiteReportCollection extends Collection
{
    public function hasMaintenanceModeOn(): bool
    {
        return $this->contains(static function (SiteReport $siteReport): bool {
            return $siteReport->maintenance_mode === true;
        });
    }
}
