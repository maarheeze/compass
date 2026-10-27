<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View;

enum BadgeType: string
{
    case DANGER = 'danger';
    case INFO = 'info';
    case NEUTRAL = 'neutral';
    case SUCCESS = 'success';
    case WARNING = 'warning';
}
