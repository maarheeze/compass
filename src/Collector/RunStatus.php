<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector;

use Maarheeze\Compass\View\LabelledEnum;

use function sprintf;

enum RunStatus: string implements LabelledEnum
{
    case FAILED = 'failed';
    case QUEUED = 'queued';
    case RUNNING = 'running';
    case SUCCEEDED = 'succeeded';

    public function labelKey(): string
    {
        return sprintf('compass::run-status.%s', $this->name);
    }
}
