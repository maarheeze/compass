<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View\Components;

use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\Site;

class SiteClientForm extends Component
{
    public readonly string $cancelUrl;
    public readonly string $updateUrl;

    public function __construct(
        Site $site,
        public readonly ?string $clientKey,
        public readonly ?string $clientPath,
        UrlGenerator $url,
        private readonly ViewFactory $view,
    ) {
        $this->cancelUrl = $url->route(RouteName::FORGE_SITE->value, $site);
        $this->updateUrl = $url->route(RouteName::FORGE_SITE_CLIENT_UPDATE->value, $site);
    }

    public function render(): View
    {
        return $this->view->make('compass::components.site-client-form');
    }
}
