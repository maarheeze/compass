<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View\Components;

use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\Site;

class SiteInspectButton extends Component
{
    public readonly string $inspectUrl;

    public function __construct(
        private readonly Site $site,
        UrlGenerator $urlGenerator,
        private readonly ViewFactory $view,
    ) {
        $this->inspectUrl = $urlGenerator->route(RouteName::SITE_INSPECT->value, $site);
    }

    public function render(): View
    {
        return $this->view->make('compass::components.site-inspect-button');
    }

    public function shouldRender(): bool
    {
        return $this->site->reports->isNotEmpty();
    }
}
