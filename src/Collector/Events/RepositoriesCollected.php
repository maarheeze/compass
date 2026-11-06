<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Events;

readonly class RepositoriesCollected
{
    public function __construct(
        public string $source,
    ) {
    }
}
