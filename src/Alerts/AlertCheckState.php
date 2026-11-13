<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts;

use Maarheeze\Compass\View\LabelledEnum;

use function sprintf;

enum AlertCheckState: string implements LabelledEnum
{
    case FAILING = 'failing';
    case MUTED = 'muted';
    case OK = 'ok';

    public function labelKey(): string
    {
        return sprintf('compass::alert-check-state.%s', $this->name);
    }
}
