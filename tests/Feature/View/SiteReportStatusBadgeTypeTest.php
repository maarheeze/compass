<?php

declare(strict_types=1);

use Maarheeze\Compass\Collector\SiteReports\SiteReportStatus;
use Maarheeze\Compass\View\BadgeType;
use Maarheeze\Compass\View\SiteReportStatusBadgeType;

test('badge type follows the site report status', function (SiteReportStatus $siteReportStatus, BadgeType $badgeType): void {
    $siteReportStatusBadgeType = new SiteReportStatusBadgeType();

    $statusBadgeType = $siteReportStatusBadgeType->badgeType($siteReportStatus);

    expect($statusBadgeType)
        ->toBe($badgeType);
})->with([
    'incompatible' => [SiteReportStatus::INCOMPATIBLE, BadgeType::DANGER],
    'installed' => [SiteReportStatus::INSTALLED, BadgeType::SUCCESS],
    'not installed' => [SiteReportStatus::NOT_INSTALLED, BadgeType::NEUTRAL],
    'unauthorised' => [SiteReportStatus::UNAUTHORISED, BadgeType::DANGER],
]);
