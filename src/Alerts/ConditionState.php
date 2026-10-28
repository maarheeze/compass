<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts;

enum ConditionState: string
{
    case ACTIVE = 'active';
    case RESOLVED = 'resolved';
}
