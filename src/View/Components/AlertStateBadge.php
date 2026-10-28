<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View\Components;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Maarheeze\Compass\Alerts\AlertStatus;
use Maarheeze\Compass\Alerts\ConditionState;
use Maarheeze\Compass\Models\Alert;
use Maarheeze\Compass\View\BadgeType;

class AlertStateBadge extends Component
{
    public function __construct(
        public readonly Alert $alert,
        private readonly ViewFactory $view,
    ) {
    }

    public function badgeType(): BadgeType
    {
        return match ($this->alert->status) {
            AlertStatus::DONE => BadgeType::NEUTRAL,
            AlertStatus::OPEN => match ($this->alert->condition) {
                ConditionState::ACTIVE => BadgeType::DANGER,
                ConditionState::RESOLVED => BadgeType::SUCCESS,
            },
        };
    }

    public function render(): View
    {
        return $this->view->make('compass::components.alert-state-badge');
    }

    public function state(): AlertStatus|ConditionState
    {
        return match ($this->alert->status) {
            AlertStatus::DONE => $this->alert->status,
            AlertStatus::OPEN => $this->alert->condition,
        };
    }
}
