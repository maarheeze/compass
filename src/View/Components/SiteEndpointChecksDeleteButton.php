<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View\Components;

use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Maarheeze\Compass\Http\EndpointPath;
use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\Site;

class SiteEndpointChecksDeleteButton extends Component
{
    public readonly string $deleteUrl;

    public function __construct(
        Site $site,
        EndpointPath $path,
        UrlGenerator $url,
        private readonly ViewFactory $view,
    ) {
        $this->deleteUrl = $url->route(RouteName::SITE_ENDPOINT_CHECKS_DELETE->value, [$site, $path->routeKey()]);
    }

    public function render(): View
    {
        return $this->view->make('compass::components.site-endpoint-checks-delete-button');
    }
}
