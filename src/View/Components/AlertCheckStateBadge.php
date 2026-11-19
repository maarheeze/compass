<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View\Components;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Maarheeze\Compass\Alerts\AlertCheckState;
use Maarheeze\Compass\Alerts\AlertCheckStateClassifier;
use Maarheeze\Compass\Models\AlertCheck;
use Maarheeze\Compass\View\AlertCheckStateBadgeType;
use Maarheeze\Compass\View\BadgeType;

class AlertCheckStateBadge extends Component
{
    public function __construct(
        public readonly AlertCheck $alertCheck,
        private readonly AlertCheckStateBadgeType $alertCheckStateBadgeType,
        private readonly AlertCheckStateClassifier $alertCheckStateClassifier,
        private readonly ViewFactory $view,
    ) {
    }

    public function badgeType(): BadgeType
    {
        return $this->alertCheckStateBadgeType->badgeType($this->state());
    }

    public function render(): View
    {
        return $this->view->make('compass::components.alert-check-state-badge');
    }

    public function state(): AlertCheckState
    {
        return $this->alertCheckStateClassifier->classify($this->alertCheck);
    }
}
