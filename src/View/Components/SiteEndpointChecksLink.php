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

class SiteEndpointChecksLink extends Component
{
    public readonly string $url;

    public function __construct(
        Site $site,
        public readonly EndpointPath $path,
        UrlGenerator $url,
        private readonly ViewFactory $view,
    ) {
        $this->url = $url->route(RouteName::SITE_ENDPOINT_CHECKS->value, [$site, $path->routeKey()]);
    }

    public function render(): View
    {
        return $this->view->make('compass::components.site-endpoint-checks-link');
    }
}
