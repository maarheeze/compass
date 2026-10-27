<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Http\Controllers;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Maarheeze\Compass\Http\EndpointPath;
use Maarheeze\Compass\Models\Site;

readonly class SiteEndpointCheckController
{
    public function __construct(
        private int $itemsPerPage,
        private ViewFactory $view,
    ) {
    }

    public function index(Site $site, string $endpointPathRouteKey): View
    {
        $endpointPath = EndpointPath::fromRouteKey($endpointPathRouteKey);

        return $this->view->make('compass::sites.endpoint-checks.index', [
            'siteEndpointChecks' => $site
                ->endpointChecks()
                ->where('path', $endpointPath->path)
                ->latest('id')
                ->paginate($this->itemsPerPage),
            'endpointPath' => $endpointPath,
            'site' => $site,
        ]);
    }
}
