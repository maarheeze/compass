<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View\Components;

use Carbon\CarbonImmutable;
use Illuminate\Config\Repository;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class DateTime extends Component
{
    public readonly string $displayDateTime;

    public function __construct(
        Repository $config,
        CarbonImmutable $time,
        private readonly ViewFactory $view,
    ) {
        $this->displayDateTime = $time
            ->setTimezone($config->string('compass.display_timezone'))
            ->toDateTimeString();
    }

    public function render(): View
    {
        return $this->view->make('compass::components.date-time');
    }
}
