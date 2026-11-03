<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Sites;

use Carbon\CarbonImmutable;

readonly class CollectedSite
{
    public function __construct(
        public string $externalId,
        public string $name,
        public string $url,
        public ?string $phpVersion,
        public ?string $repositoryUrl,
        public ?string $repositoryBranch,
        public ?bool $autoDeploy,
        public string $serverName,
        public ?string $serverIpAddress,
        public ?DeploymentStatus $deploymentStatus,
        public ?string $deployedCommitHash,
        public ?CarbonImmutable $deploymentStartedAt,
        public ?CarbonImmutable $deploymentEndedAt,
    ) {
    }
}
