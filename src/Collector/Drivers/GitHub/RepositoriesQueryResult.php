<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Drivers\GitHub;

use Maarheeze\Compass\Collections\CollectedRepositoryCollection;

readonly class RepositoriesQueryResult
{
    public function __construct(
        public CollectedRepositoryCollection $collectedRepositories,
        public ?string $nextCursor,
    ) {
    }
}
