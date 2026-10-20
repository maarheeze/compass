<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector;

readonly class GitHubRepository
{
    public function __construct(
        public string $name,
        public string $defaultBranch,
    ) {
    }
}
