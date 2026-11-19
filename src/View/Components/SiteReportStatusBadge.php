<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View\Components;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Maarheeze\Compass\Collector\SiteReports\SiteReportStatus;
use Maarheeze\Compass\View\BadgeType;
use Maarheeze\Compass\View\SiteReportStatusBadgeType;

class SiteReportStatusBadge extends Component
{
    public function __construct(
        public readonly SiteReportStatus $status,
        private readonly SiteReportStatusBadgeType $siteReportStatusBadgeType,
        private readonly ViewFactory $view,
    ) {
    }

    public function badgeType(): BadgeType
    {
        return $this->siteReportStatusBadgeType->badgeType($this->status);
    }

    public function render(): View
    {
        return $this->view->make('compass::components.site-report-status-badge');
    }
}
