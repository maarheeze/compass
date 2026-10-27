<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View\Components;

use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\Site;

class SiteLink extends Component
{
    public readonly string $url;

    public function __construct(
        public readonly Site $site,
        UrlGenerator $url,
        private readonly ViewFactory $view,
    ) {
        $this->url = $url->route(RouteName::FORGE_SITE->value, $site);
    }

    public function render(): View
    {
        return $this->view->make('compass::components.site-link');
    }
}
