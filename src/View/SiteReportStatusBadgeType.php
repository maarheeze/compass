<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View;

use Maarheeze\Compass\Collector\SiteReports\SiteReportStatus;

readonly class SiteReportStatusBadgeType
{
    public function badgeType(SiteReportStatus $siteReportStatus): BadgeType
    {
        return match ($siteReportStatus) {
            SiteReportStatus::INCOMPATIBLE, SiteReportStatus::UNAUTHORISED => BadgeType::DANGER,
            SiteReportStatus::INSTALLED => BadgeType::SUCCESS,
            SiteReportStatus::NOT_INSTALLED => BadgeType::NEUTRAL,
        };
    }
}
