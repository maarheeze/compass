<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View\Components;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Maarheeze\Compass\Collector\SiteReports\SiteReportsCollectorManager;
use Maarheeze\Compass\Models\SiteReport;

class SiteReportDetails extends Component
{
    public readonly string $key;
    public readonly string $path;

    public function __construct(
        public readonly SiteReport $siteReport,
        SiteReportsCollectorManager $siteReportsCollectorManager,
        private readonly ViewFactory $view,
    ) {
        $siteReportsCollector = $siteReportsCollectorManager->collector($siteReport->source);

        $this->key = $siteReportsCollector->getKey($siteReport);
        $this->path = $siteReportsCollector->getPath($siteReport);
    }

    public function render(): View
    {
        return $this->view->make('compass::components.site-report-details');
    }
}
