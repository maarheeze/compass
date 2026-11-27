<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Analyser\Processes;

use Maarheeze\Compass\View\LabelledEnum;

use function sprintf;

enum QueueWorkerHealth implements LabelledEnum
{
    case NOT_NEEDED;
    case NOT_RUNNING;
    case RUNNING;
    case UNKNOWN;

    public function labelKey(): string
    {
        return sprintf('compass::queue-worker-health.%s', $this->name);
    }
}
