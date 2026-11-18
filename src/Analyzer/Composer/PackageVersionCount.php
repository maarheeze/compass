<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Analyzer\Composer;

readonly class PackageVersionCount
{
    public function __construct(
        public string $version,
        public int $count,
    ) {
    }
}
