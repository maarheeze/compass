<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Forge;

use Carbon\CarbonImmutable;

readonly class ForgeDeployment
{
    public function __construct(
        public DeploymentStatus $status,
        public ?string $commitHash,
        public ?string $commitMessage,
        public CarbonImmutable $startedAt,
        public ?CarbonImmutable $endedAt,
    ) {
    }
}
