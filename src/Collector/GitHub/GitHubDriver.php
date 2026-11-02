<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\GitHub;

use Maarheeze\Compass\Collector\SourceDriver;

readonly class GitHubDriver implements SourceDriver
{
    public function __construct(
        public string $organization,
        public string $token,
    ) {
    }
}
