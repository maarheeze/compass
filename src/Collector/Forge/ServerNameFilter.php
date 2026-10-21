<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Forge;

use function preg_match;

readonly class ServerNameFilter
{
    public function __construct(
        private string $pattern,
    ) {
    }

    public function matches(ForgeSite $forgeSite): bool
    {
        return preg_match($this->pattern, $forgeSite->serverName) === 1;
    }
}
