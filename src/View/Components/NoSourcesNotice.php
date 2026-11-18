<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View\Components;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Maarheeze\Compass\Collector\SourceConfigs;

class NoSourcesNotice extends Component
{
    public function __construct(
        private readonly SourceConfigs $sourceConfigs,
        private readonly ViewFactory $view,
    ) {
    }

    public function render(): View
    {
        return $this->view->make('compass::components.no-sources-notice');
    }

    public function shouldRender(): bool
    {
        return $this->sourceConfigs->all() === [];
    }
}
