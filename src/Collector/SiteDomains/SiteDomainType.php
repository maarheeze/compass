<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\SiteDomains;

use Maarheeze\Compass\View\LabelledEnum;

use function sprintf;

enum SiteDomainType: string implements LabelledEnum
{
    case ALIAS = 'alias';
    case PRIMARY = 'primary';
    case REVERB = 'reverb';

    public function labelKey(): string
    {
        return sprintf('compass::site-domain-type.%s', $this->name);
    }
}
