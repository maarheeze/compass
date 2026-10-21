<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Composer;

enum PackageSection: string
{
    case DEVELOPMENT = 'DEVELOPMENT';
    case PRODUCTION = 'PRODUCTION';
}
