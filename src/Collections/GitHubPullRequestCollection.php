<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collections;

use Illuminate\Support\Collection;
use Maarheeze\Compass\Collector\GitHub\GitHubPullRequest;

/**
 * @extends Collection<int, GitHubPullRequest>
 */
class GitHubPullRequestCollection extends Collection
{
}
