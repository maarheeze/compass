<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts;

enum AlertAction: string
{
    case OPEN = 'open';
    case RECUR = 'recur';
    case RESOLVE = 'resolve';
}
