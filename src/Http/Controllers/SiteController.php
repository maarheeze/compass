<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Http\Controllers;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Maarheeze\Compass\Collector\Sites\MissingSiteResolver;
use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\Alert;
use Maarheeze\Compass\Models\Site;
use Throwable;

readonly class SiteController
{
    public function __construct(
        private MissingSiteResolver $missingSiteResolver,
        private Redirector $redirector,
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

    public function index(): View
    {
        return $this->view->make('compass::sites.index', [
            'sites' => Site::query()
                ->with(['reports', 'repository'])
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function show(Site $site): View
    {
        $site->load(['reports.endpoints', 'repository']);

        return $this->view->make('compass::sites.show', [
            'activeSiteEndpointChecks' => $site
                ->endpointChecks()
                ->latestPerPath()
                ->reported()
                ->withOnlineSince()
                ->orderBy('path')
                ->get(),
            'alerts' => Alert::query()
                ->ofSite($site)
                ->with('assignee')
                ->withSubject()
                ->latest()
                ->get(),
            'inactiveSiteEndpointChecks' => $site
                ->endpointChecks()
                ->latestPerPath()
                ->notReported()
                ->withOnlineSince()
                ->orderBy('path')
                ->get(),
            'site' => $site,
        ]);
    }
}
