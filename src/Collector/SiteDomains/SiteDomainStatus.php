<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\SiteDomains;

use Maarheeze\Compass\View\LabelledEnum;

use function sprintf;

enum SiteDomainStatus: string implements LabelledEnum
{
    case CONNECTING = 'connecting';
    case DISABLED = 'disabled';
    case DISABLING = 'disabling';
    case ENABLED = 'enabled';
    case ENABLING = 'enabling';
    case PENDING = 'pending';
    case REMOVING = 'removing';
    case SECURING = 'securing';
    case UPDATING = 'updating';

    public function labelKey(): string
    {
        return sprintf('compass::site-domain-status.%s', $this->name);
    }
}
