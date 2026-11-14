<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View\Components;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Psr\Clock\ClockInterface;

class RelativeTime extends Component
{
    public readonly string $isoDateTime;
    public readonly string $relativeTime;

    public function __construct(
        ClockInterface $clock,
        CarbonImmutable $time,
        private readonly ViewFactory $view,
    ) {
        $this->isoDateTime = $time->toIso8601String();
        $this->relativeTime = $time->diffForHumans($clock->now(), CarbonInterface::DIFF_RELATIVE_TO_NOW);
    }

    public function render(): View
    {
        return $this->view->make('compass::components.relative-time');
    }
}
