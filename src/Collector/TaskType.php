<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector;

use Maarheeze\Compass\View\LabelledEnum;

use function sprintf;

enum TaskType: string implements LabelledEnum
{
    case SITE_ENDPOINTS = 'site_endpoints';

    public function labelKey(): string
    {
        return sprintf('compass::task-type.%s', $this->name);
    }
}
