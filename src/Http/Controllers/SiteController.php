<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Http\Controllers;

use Illuminate\Contracts\Translation\Translator;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Maarheeze\Compass\Collector\Sites\MissingSiteResolver;
use Maarheeze\Compass\Http\Requests\SiteFilterRequest;
use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\Alert;
use Maarheeze\Compass\Models\AlertCheck;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\Models\SiteEndpoint;
use Throwable;

readonly class SiteController
{
    public function __construct(
        private MissingSiteResolver $missingSiteResolver,
        private Redirector $redirector,
        private Translator $translator,
        private ViewFactory $view,
    ) {
    }

    /**
     * @throws Throwable
     */
    public function delete(Site $site): RedirectResponse
    {
        $this->missingSiteResolver->delete($site);

        return $this->redirector->route(RouteName::DASHBOARD);
    }

    public function ignore(Site $site): RedirectResponse
    {
        $this->missingSiteResolver->ignore($site);

        return $this->redirector->route(RouteName::DASHBOARD);
    }

    public function index(SiteFilterRequest $siteFilterRequest): View
    {
        $sites = Site::query()->listed();
        $siteFilter = $siteFilterRequest->siteFilter();

        return $this->view->make('compass::sites.index', [
            'phpVersionFilter' => $siteFilter->phpVersionFilter($sites, $this->translator),
            'serverFilter' => $siteFilter->serverFilter($sites, $this->translator),
            'sites' => $siteFilter->sites($sites),
        ]);
    }

    public function show(Site $site): View
    {
        $site->load(['reports', 'repository']);

        return $this->view->make('compass::sites.show', [
            'alertChecks' => AlertCheck::query()->ofSiteOrItsReports($site)->listed(),
            'alerts' => Alert::query()->ofSite($site)->listed(),
            'inactiveSiteEndpointChecks' => $site->endpointChecks()->listed(),
            'reportedSiteEndpoints' => SiteEndpoint::query()->ofSite($site)->listed(),
            'site' => $site,
        ]);
    }
}
