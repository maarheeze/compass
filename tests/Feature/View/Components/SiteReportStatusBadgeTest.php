<?php

declare(strict_types=1);

use Maarheeze\Compass\Collector\SiteReports\SiteReportStatus;
use Maarheeze\Compass\View\Components\SiteReportStatusBadge;
use Maarheeze\Compass\View\SiteReportStatusBadgeType;

test('badge type is the badge type of the status', function (): void {
    $siteReportStatus = fake()->randomElement(SiteReportStatus::cases());

    $siteReportStatusBadge = $this->app->make(SiteReportStatusBadge::class, ['status' => $siteReportStatus]);

    expect($siteReportStatusBadge->badgeType())
        ->toBe(new SiteReportStatusBadgeType()->badgeType($siteReportStatus));
});
