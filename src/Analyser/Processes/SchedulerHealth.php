<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Analyser\Processes;

use Maarheeze\Compass\View\LabelledEnum;

use function sprintf;

enum SchedulerHealth implements LabelledEnum
{
    case NOT_RUNNING;
    case NO_TASKS;
    case RUNNING;
    case UNKNOWN;

    public function isJudged(): bool
    {
        return match ($this) {
            self::NOT_RUNNING, self::RUNNING => true,
            self::NO_TASKS, self::UNKNOWN => false,
        };
    }

    public function labelKey(): string
    {
        return sprintf('compass::scheduler-health.%s', $this->name);
    }
}
