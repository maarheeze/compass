<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Forge;

readonly class ForgeSite
{
    public function __construct(
        public string $forgeId,
        public string $name,
        public string $url,
        public SiteStatus $status,
        public ?string $phpVersion,
        public ?string $repositoryUrl,
        public ?string $repositoryBranch,
        public string $serverName,
        public ?string $serverIpAddress,
        public ?ForgeDeployment $latestDeployment,
    ) {
    }
}
