<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts;

enum AlertAction: string
{
    case CLEAR = 'clear';
    case OPEN = 'open';
    case RECUR = 'recur';
}
