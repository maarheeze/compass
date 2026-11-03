<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Forge;

use Maarheeze\Compass\Collector\CollectorDriver;

readonly class ForgeDriver implements CollectorDriver
{
    public function __construct(
        public string $organization,
        public string $token,
        public int $autoDeployGraceMinutes,
    ) {
    }
}
