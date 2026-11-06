<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Http;

readonly class AssigneeFilter
{
    public function __construct(
        public string $source,
        public string $login,
    ) {
    }
}
