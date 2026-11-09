<?php

declare(strict_types=1);

use Maarheeze\Compass\Collector\SiteReports\SiteReportStatus;
use Maarheeze\Compass\View\BadgeType;
use Maarheeze\Compass\View\Components\SiteReportStatusBadge;

test('badge type follows the site report status', function (?SiteReportStatus $siteReportStatus, BadgeType $badgeType): void {
    $siteReportStatusBadge = $this->app->make(SiteReportStatusBadge::class, ['status' => $siteReportStatus]);

    expect($siteReportStatusBadge->badgeType())
        ->toBe($badgeType);
})->with([
    'incompatible' => [SiteReportStatus::INCOMPATIBLE, BadgeType::DANGER],
    'installed' => [SiteReportStatus::INSTALLED, BadgeType::SUCCESS],
    'not installed' => [SiteReportStatus::NOT_INSTALLED, BadgeType::NEUTRAL],
    'unauthorized' => [SiteReportStatus::UNAUTHORIZED, BadgeType::DANGER],
    'not checked' => [null, BadgeType::NEUTRAL],
]);
