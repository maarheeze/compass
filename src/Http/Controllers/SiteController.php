<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Http\Controllers;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Maarheeze\Compass\Collector\Client\ClientKey;
use Maarheeze\Compass\Collector\Client\ClientUrlFactory;
use Maarheeze\Compass\Collector\Forge\MissingSiteResolver;
use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\Alert;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\Models\SiteEndpoint;
use Throwable;

readonly class SiteController
{
    public function __construct(
        private ClientKey $clientKey,
        private ClientUrlFactory $clientUrlFactory,
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
                ->with('repository')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function show(Site $site): View
    {
        $site->load(['endpoints', 'repository']);

        return $this->view->make('compass::sites.show', [
            'alerts' => Alert::query()
                ->ofSite($site)
                ->latest()
                ->get()
                ->loadMorph('subject', [SiteEndpoint::class => ['site']]),
            'clientKey' => $this->clientKey->find($site),
            'clientPath' => $this->clientUrlFactory->path($site),
            'latestSiteEndpointChecks' => $site
                ->endpointChecks()
                ->latestPerPath()
                ->orderBy('path')
                ->get(),
            'site' => $site,
        ]);
    }
}
