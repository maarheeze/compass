<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View\Components;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Maarheeze\Compass\Collector\Client\ClientStatus;
use Maarheeze\Compass\View\BadgeType;

class ClientStatusBadge extends Component
{
    public function __construct(
        public readonly ?ClientStatus $status,
        private readonly ViewFactory $view,
    ) {
    }

    public function badgeType(): BadgeType
    {
        return match ($this->status) {
            ClientStatus::INCOMPATIBLE, ClientStatus::UNAUTHORIZED => BadgeType::DANGER,
            ClientStatus::INSTALLED => BadgeType::SUCCESS,
            ClientStatus::NOT_INSTALLED, ClientStatus::UNREACHABLE, null => BadgeType::NEUTRAL,
        };
    }

    public function render(): View
    {
        return $this->view->make('compass::components.client-status-badge');
    }
}
