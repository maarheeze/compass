<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts;

enum AlertCondition: string
{
    case GONE = 'gone';
    case PRESENT = 'present';
}
