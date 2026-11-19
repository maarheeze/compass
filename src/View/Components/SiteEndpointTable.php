<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View\Components;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Maarheeze\Compass\Collections\SiteEndpointCheckCollection;
use Maarheeze\Compass\Collections\SiteEndpointCollection;
use Maarheeze\Compass\Models\Site;

class SiteEndpointTable extends Component
{
    public readonly int $downSiteEndpointCount;
    public readonly SiteEndpointCollection $downSiteEndpoints;
    public readonly SiteEndpointCollection $notDownSiteEndpoints;
    public readonly int $upSiteEndpointCount;

    public function __construct(
        public readonly Site $site,
        SiteEndpointCollection $reportedSiteEndpoints,
        public readonly SiteEndpointCheckCollection $inactiveSiteEndpointChecks,
        private readonly ViewFactory $view,
    ) {
        $this->downSiteEndpoints = $reportedSiteEndpoints->down();
        $this->downSiteEndpointCount = $this->downSiteEndpoints->count();
        $this->notDownSiteEndpoints = $reportedSiteEndpoints->notDown();
        $this->upSiteEndpointCount = $reportedSiteEndpoints->up()->count();
    }

    public function render(): View
    {
        return $this->view->make('compass::components.site-endpoint-table');
    }
}
