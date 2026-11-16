<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View\Components;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Maarheeze\Compass\Collector\SiteReports\SiteReportStatus;
use Maarheeze\Compass\Models\SiteReport;

class SiteClientInstallNotice extends Component
{
    public function __construct(
        private readonly SiteReport $siteReport,
        private readonly ViewFactory $view,
    ) {
    }

    public function render(): View
    {
        return $this->view->make('compass::components.site-client-install-notice');
    }

    public function shouldRender(): bool
    {
        return $this->siteReport->status === SiteReportStatus::NOT_INSTALLED;
    }
}
