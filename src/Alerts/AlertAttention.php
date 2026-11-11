<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts;

enum AlertAttention: string
{
    case ACTIVE = 'active';
    case MUTED = 'muted';
    case NONE = 'none';
}
