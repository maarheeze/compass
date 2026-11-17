<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View\Components;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Maarheeze\Compass\Alerts\AlertLevel;
use Maarheeze\Compass\Alerts\AlertTypes;
use Maarheeze\Compass\View\AlertLevelBadgeType;
use Maarheeze\Compass\View\BadgeType;

class AlertLevelBadge extends Component
{
    public readonly AlertLevel $alertLevel;
    public readonly BadgeType $badgeType;

    public function __construct(
        string $identifier,
        AlertLevelBadgeType $alertLevelBadgeType,
        AlertTypes $alertTypes,
        private readonly ViewFactory $view,
    ) {
        $this->alertLevel = $alertTypes->alertType($identifier)->level();
        $this->badgeType = $alertLevelBadgeType->badgeType($this->alertLevel);
    }

    public function render(): View
    {
        return $this->view->make('compass::components.alert-level-badge');
    }
}
