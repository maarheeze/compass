<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Composer;

use Maarheeze\Compass\View\LabelledEnum;

use function sprintf;

enum PackageSection: string implements LabelledEnum
{
    case DEVELOPMENT = 'DEVELOPMENT';
    case PRODUCTION = 'PRODUCTION';

    public function labelKey(): string
    {
        return sprintf('compass::package-section.%s', $this->name);
    }
}
