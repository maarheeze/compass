<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View\Components;

use Carbon\CarbonInterface;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Time extends Component
{
    public readonly string $displayTime;

    public function __construct(
        CarbonInterface $time,
        private readonly ViewFactory $view,
    ) {
        $this->displayTime = $time->diffForHumans();
    }

    public function render(): View
    {
        return $this->view->make('compass::components.time');
    }
}
