<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts;

enum AlertStatus: string
{
    case DONE = 'done';
    case OPEN = 'open';
}
