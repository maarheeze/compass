<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View\Components;

use Illuminate\Config\Repository;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Webmozart\Assert\Assert;

class PackageVersions extends Component
{
    /** @var array<array-key, string> */
    public readonly array $packageNames;

    public function __construct(
        Repository $config,
        private readonly ViewFactory $view,
    ) {
        $packageNames = $config->array('compass.dashboard_packages');
        Assert::allString($packageNames);
        $this->packageNames = $packageNames;
    }

    public function render(): View
    {
        return $this->view->make('compass::components.package-versions');
    }

    public function shouldRender(): bool
    {
        return $this->packageNames !== [];
    }
}
