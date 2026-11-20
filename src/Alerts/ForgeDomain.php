<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts;

use function str_ends_with;

readonly class ForgeDomain
{
    private const string SUFFIX = '.on-forge.com';

    public function covers(string $hostname): bool
    {
        return str_ends_with($hostname, self::SUFFIX);
    }
}
