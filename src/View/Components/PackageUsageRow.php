<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View\Components;

use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Maarheeze\Compass\Collector\Composer\PackageSection;
use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\Repository;

class PackageUsageRow extends Component
{
    public readonly string $url;

    public function __construct(
        public readonly Repository $repository,
        public readonly string $version,
        public readonly PackageSection $section,
        UrlGenerator $url,
        private readonly ViewFactory $view,
    ) {
        $this->url = $url->route(RouteName::REPOSITORY->value, $repository);
    }

    public function render(): View
    {
        return $this->view->make('compass::components.package-usage-row');
    }
}
