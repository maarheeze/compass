<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View;

use Maarheeze\Compass\Alerts\AlertLevel;

readonly class AlertLevelBadgeType
{
    public function badgeType(AlertLevel $alertLevel): BadgeType
    {
        return match ($alertLevel) {
            AlertLevel::CRITICAL => BadgeType::DANGER,
            AlertLevel::NOTICE => BadgeType::INFO,
            AlertLevel::WARNING => BadgeType::WARNING,
        };
    }
}
