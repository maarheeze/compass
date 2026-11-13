<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View\Components;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Maarheeze\Compass\Collections\AlertCheckCollection;

class AlertCheckTable extends Component
{
    public function __construct(
        public readonly AlertCheckCollection $alertChecks,
        private readonly ViewFactory $view,
    ) {
    }

    public function render(): View
    {
        return $this->view->make('compass::components.alert-check-table');
    }
}
