<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View\Components;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Maarheeze\Compass\Analyzer\Alert;

class AlertItem extends Component
{
    public function __construct(
        public readonly Alert $alert,
        private readonly ViewFactory $view,
    ) {
    }

    public function render(): View
    {
        return $this->view->make($this->alert->viewName());
    }
}
