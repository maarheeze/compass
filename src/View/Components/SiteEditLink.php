<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View\Components;

use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\Site;

class SiteEditLink extends Component
{
    public readonly string $url;

    public function __construct(
        Site $site,
        UrlGenerator $urlGenerator,
        private readonly ViewFactory $view,
    ) {
        $this->url = $urlGenerator->route(RouteName::SITE_EDIT->value, $site);
    }

    public function render(): View
    {
        return $this->view->make('compass::components.site-edit-link');
    }
}
