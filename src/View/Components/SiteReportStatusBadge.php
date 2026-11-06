<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View\Components;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Maarheeze\Compass\Collector\SiteReports\SiteReportStatus;
use Maarheeze\Compass\View\BadgeType;

class SiteReportStatusBadge extends Component
{
    public function __construct(
        public readonly ?SiteReportStatus $status,
        private readonly ViewFactory $view,
    ) {
    }

    public function badgeType(): BadgeType
    {
        return match ($this->status) {
            SiteReportStatus::INCOMPATIBLE, SiteReportStatus::UNAUTHORIZED => BadgeType::DANGER,
            SiteReportStatus::INSTALLED => BadgeType::SUCCESS,
            SiteReportStatus::NOT_INSTALLED, null => BadgeType::NEUTRAL,
        };
    }

    public function render(): View
    {
        return $this->view->make('compass::components.site-report-status-badge');
    }
}
