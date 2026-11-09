<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View\Components;

use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\SiteReport;

class SiteReportForm extends Component
{
    public readonly string $cancelUrl;
    public readonly string $updateUrl;

    public function __construct(
        SiteReport $siteReport,
        public readonly ?string $key,
        public readonly ?string $path,
        UrlGenerator $urlGenerator,
        private readonly ViewFactory $view,
    ) {
        $this->cancelUrl = $urlGenerator->route(RouteName::SITE->value, $siteReport->site);
        $this->updateUrl = $urlGenerator->route(RouteName::SITE_REPORT_UPDATE->value, $siteReport);
    }

    public function render(): View
    {
        return $this->view->make('compass::components.site-report-form');
    }
}
