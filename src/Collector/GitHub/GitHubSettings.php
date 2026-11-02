<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\GitHub;

readonly class GitHubSettings
{
    public function __construct(
        public string $organization,
        public string $token,
    ) {
    }
}
