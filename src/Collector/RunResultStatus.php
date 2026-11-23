<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector;

use Maarheeze\Compass\View\LabelledEnum;

use function sprintf;

enum RunResultStatus: string implements LabelledEnum
{
    case FAILED = 'failed';
    case SUCCEEDED = 'succeeded';

    public function labelKey(): string
    {
        return sprintf('compass::run-result-status.%s', $this->name);
    }
}
