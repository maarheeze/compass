<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Http\Controllers;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Maarheeze\Compass\Http\EndpointPath;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\Models\SiteEndpointCheck;

readonly class SiteEndpointCheckController
{
    public function __construct(
        private ViewFactory $view,
    ) {
    }

    public function index(Site $site, string $endpointPathRouteKey): View
    {
        $endpointPath = EndpointPath::fromRouteKey($endpointPathRouteKey);

        return $this->view->make('compass::sites.endpoint-checks.index', [
            'siteEndpointChecks' => SiteEndpointCheck::query()
                ->where('site_id', $site->id)
                ->where('path', $endpointPath->path)
                ->latest('id')
                ->paginate(50),
            'endpointPath' => $endpointPath,
            'site' => $site,
        ]);
    }
}
