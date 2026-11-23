<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View\Components;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Maarheeze\Compass\Collector\RunResultStatus;
use Maarheeze\Compass\View\BadgeType;

class RunResultStatusBadge extends Component
{
    public function __construct(
        public readonly RunResultStatus $status,
        private readonly ViewFactory $view,
    ) {
    }

    public function badgeType(): BadgeType
    {
        return match ($this->status) {
            RunResultStatus::FAILED => BadgeType::DANGER,
            RunResultStatus::SUCCEEDED => BadgeType::SUCCESS,
        };
    }

    public function render(): View
    {
        return $this->view->make('compass::components.run-result-status-badge');
    }
}
