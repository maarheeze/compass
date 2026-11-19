<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\SiteEndpoint;

use Maarheeze\Compass\View\LabelledEnum;

use function sprintf;

enum SiteEndpointState: string implements LabelledEnum
{
    case DOWN = 'down';
    case UP = 'up';

    public function labelKey(): string
    {
        return sprintf('compass::site-endpoint-state.%s', $this->name);
    }
}
