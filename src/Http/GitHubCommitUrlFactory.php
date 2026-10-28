<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Http;

use Maarheeze\Compass\Models\Repository;

use function sprintf;

readonly class GitHubCommitUrlFactory
{
    public function create(Repository $repository, string $commitHash): string
    {
        return sprintf('%s/commit/%s', $repository->url, $commitHash);
    }
}
