<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\GitHub;

use Maarheeze\Compass\Collector\CollectorDriver;

readonly class GitHubDriver implements CollectorDriver
{
    public function __construct(
        public string $organization,
        public string $token,
    ) {
    }
}
