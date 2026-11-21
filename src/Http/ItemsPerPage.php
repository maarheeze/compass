<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Http;

readonly class ItemsPerPage
{
    public function __construct(
        public int $value,
    ) {
    }
}
