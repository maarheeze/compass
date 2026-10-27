<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View\Components;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Maarheeze\Compass\Collector\RunStatus;
use Maarheeze\Compass\View\BadgeType;

class RunStatusBadge extends Component
{
    public function __construct(
        public readonly RunStatus $status,
        private readonly ViewFactory $view,
    ) {
    }

    public function badgeType(): BadgeType
    {
        return match ($this->status) {
            RunStatus::FAILED => BadgeType::DANGER,
            RunStatus::RUNNING => BadgeType::INFO,
            RunStatus::SUCCEEDED => BadgeType::SUCCESS,
        };
    }

    public function render(): View
    {
        return $this->view->make('compass::components.run-status-badge');
    }
}
