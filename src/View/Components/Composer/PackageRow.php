<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View\Components\Composer;

use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Maarheeze\Compass\Http\RouteName;

class PackageRow extends Component
{
    public readonly string $url;

    public function __construct(
        public readonly string $packageName,
        UrlGenerator $url,
        private readonly ViewFactory $view,
    ) {
        $this->url = $url->route(RouteName::PACKAGE->value, $packageName);
    }

    public function render(): View
    {
        return $this->view->make('compass::components.composer.package-row');
    }
}
