<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Http\Controllers;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Maarheeze\Compass\Collector\SiteEndpoint\EndpointPath;
use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\Site;

readonly class SiteEndpointCheckController
{
    public function __construct(
        private int $itemsPerPage,
        private Redirector $redirector,
        private ViewFactory $view,
    ) {
    }

    public function delete(Site $site, EndpointPath $endpointPath): RedirectResponse
    {
        $site
            ->endpointChecks()
            ->ofPath($endpointPath)
            ->delete();

        return $this->redirector->route(RouteName::SITE, $site);
    }

    public function index(Site $site, EndpointPath $endpointPath): View
    {
        return $this->view->make('compass::sites.endpoint-checks.index', [
            'siteEndpointChecks' => $site
                ->endpointChecks()
                ->ofPath($endpointPath)
                ->latest('id')
                ->paginate($this->itemsPerPage),
            'endpointPath' => $endpointPath,
            'site' => $site,
        ]);
    }
}
