<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View\Components;

use Illuminate\Config\Repository;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ApplicationName extends Component
{
    public readonly string $applicationName;

    public function __construct(
        Repository $config,
        private readonly ViewFactory $view,
    ) {
        $this->applicationName = $config->string('app.name');
    }

    public function render(): View
    {
        return $this->view->make('compass::components.application-name');
    }
}
