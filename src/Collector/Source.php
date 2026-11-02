<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector;

readonly class Source
{
    public function __construct(
        public string $key,
        public SourceDriver $driver,
    ) {
    }
}
