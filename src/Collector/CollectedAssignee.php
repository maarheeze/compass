<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector;

readonly class CollectedAssignee
{
    public function __construct(
        public string $login,
        public string $avatarUrl,
    ) {
    }
}
