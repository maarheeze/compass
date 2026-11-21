<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View\Components;

use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\Site;

class SiteForm extends Component
{
    public readonly string $cancelUrl;
    public readonly string $updateUrl;

    public function __construct(
        Site $site,
        public readonly ?string $key,
        UrlGenerator $urlGenerator,
        private readonly ViewFactory $view,
    ) {
        $this->cancelUrl = $urlGenerator->route(RouteName::SITE->value, $site);
        $this->updateUrl = $urlGenerator->route(RouteName::SITE_UPDATE->value, $site);
    }

    public function render(): View
    {
        return $this->view->make('compass::components.site-form');
    }
}
