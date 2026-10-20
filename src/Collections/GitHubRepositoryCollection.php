<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collections;

use Illuminate\Support\Collection;
use Maarheeze\Compass\Collector\GitHub\GitHubRepository;

/**
 * @extends Collection<int, GitHubRepository>
 */
class GitHubRepositoryCollection extends Collection
{
}
