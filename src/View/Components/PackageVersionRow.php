<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View\Components;

use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Maarheeze\Compass\Analyser\Composer\PackageVersionCount;
use Maarheeze\Compass\Http\RouteName;

class PackageVersionRow extends Component
{
    public readonly string $url;

    public function __construct(
        public readonly string $packageName,
        public readonly PackageVersionCount $versionCount,
        UrlGenerator $urlGenerator,
        private readonly ViewFactory $view,
    ) {
        $this->url = $urlGenerator->route(RouteName::PACKAGE_VERSION->value, [
            'packageName' => $packageName,
            'version' => $versionCount->version,
        ]);
    }

    public function render(): View
    {
        return $this->view->make('compass::components.package-version-row');
    }
}
