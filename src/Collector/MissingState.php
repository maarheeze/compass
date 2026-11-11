<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector;

use Maarheeze\Compass\View\LabelledEnum;

use function sprintf;

enum MissingState implements LabelledEnum
{
    case COLLECTED;
    case IGNORED;
    case MISSING;

    public function labelKey(): string
    {
        return sprintf('compass::missing-state.%s', $this->name);
    }
}
