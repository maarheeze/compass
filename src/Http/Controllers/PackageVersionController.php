<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Http\Controllers;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Maarheeze\Compass\Analyser\Composer\PackageUsageFinder;

readonly class PackageVersionController
{
    public function __construct(
        private PackageUsageFinder $packageUsageFinder,
        private ViewFactory $view,
    ) {
    }

    public function show(string $packageName, string $version): View
    {
        return $this->view->make('compass::packages.version', [
            'packageName' => $packageName,
            'version' => $version,
            'lockedPackages' => $this->packageUsageFinder->findLockedPackagesOfVersion($packageName, $version),
        ]);
    }
}
