<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector;

enum RunStatus: string
{
    case FAILED = 'failed';
    case RUNNING = 'running';
    case SUCCEEDED = 'succeeded';
}
