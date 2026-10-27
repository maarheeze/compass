<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View\Components;

use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\Site;

class SiteClientEditLink extends Component
{
    public readonly string $url;

    public function __construct(
        Site $site,
        UrlGenerator $url,
        private readonly ViewFactory $view,
    ) {
        $this->url = $url->route(RouteName::FORGE_SITE_CLIENT_EDIT->value, $site);
    }

    public function render(): View
    {
        return $this->view->make('compass::components.site-client-edit-link');
    }
}
