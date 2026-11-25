<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector;

enum QueuePriority: string
{
    case HIGH = 'high';
    case LOW = 'low';
    case MEDIUM = 'medium';
}
