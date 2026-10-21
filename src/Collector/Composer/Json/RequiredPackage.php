<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Composer\Json;

use Maarheeze\Compass\Collector\Composer\PackageSection;

readonly class RequiredPackage
{
    public function __construct(
        public string $name,
        public string $constraint,
        public PackageSection $section,
    ) {
    }
}
