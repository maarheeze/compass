<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View\Components;

use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Maarheeze\Compass\Analyzer\Composer\PackageUsageFinder;
use Maarheeze\Compass\Collections\PackageVersionCountCollection;
use Maarheeze\Compass\Http\RouteName;

class PackageVersionsTable extends Component
{
    public readonly string $url;
    public readonly PackageVersionCountCollection $versionCounts;

    public function __construct(
        public readonly string $packageName,
        PackageUsageFinder $packageUsageFinder,
        UrlGenerator $urlGenerator,
        private readonly ViewFactory $view,
    ) {
        $this->url = $urlGenerator->route(RouteName::PACKAGE->value, $packageName);
        $this->versionCounts = $packageUsageFinder->findLockedPackagesOfProjects($packageName)->versionCounts();
    }

    public function render(): View
    {
        return $this->view->make('compass::components.package-versions-table');
    }
}
