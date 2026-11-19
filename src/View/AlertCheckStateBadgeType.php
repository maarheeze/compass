<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View;

use Maarheeze\Compass\Alerts\AlertCheckState;

readonly class AlertCheckStateBadgeType
{
    public function badgeType(AlertCheckState $alertCheckState): BadgeType
    {
        return match ($alertCheckState) {
            AlertCheckState::FAILING => BadgeType::DANGER,
            AlertCheckState::MUTED => BadgeType::NEUTRAL,
            AlertCheckState::OK => BadgeType::SUCCESS,
        };
    }
}
