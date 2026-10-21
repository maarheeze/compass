<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Composer\Lock;

use Maarheeze\Compass\Collector\Composer\PackageSection;

readonly class LockedPackage
{
    public function __construct(
        public string $name,
        public string $version,
        public PackageSection $section,
    ) {
    }
}
