<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Http\Controllers;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Maarheeze\Compass\Collector\Forge\MissingSiteResolver;
use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\Site;

readonly class SiteController
{
    public function __construct(
        private MissingSiteResolver $missingSiteResolver,
        private Redirector $redirector,
        private ViewFactory $view,
    ) {
    }

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
        $site->load('repository');

        return $this->view->make('compass::sites.show', [
            'site' => $site,
        ]);
    }
}
