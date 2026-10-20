<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\GitHub;

use Maarheeze\Compass\Collections\GitHubRepositoryCollection;

readonly class RepositoriesQueryResult
{
    public function __construct(
        public GitHubRepositoryCollection $gitHubRepositories,
        public ?string $nextCursor,
    ) {
    }
}
