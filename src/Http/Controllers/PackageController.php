<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Http\Controllers;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Maarheeze\Compass\Analyzer\Composer\PackageUsageFinder;

readonly class PackageController
{
    public function __construct(
        private PackageUsageFinder $packageUsageFinder,
        private ViewFactory $view,
    ) {
    }

    public function show(string $packageName): View
    {
        return $this->view->make('compass::packages.show', [
            'packageName' => $packageName,
            'packageUsages' => $this->packageUsageFinder->find($packageName),
        ]);
    }
}
