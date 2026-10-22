<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collections;

use Illuminate\Support\Collection;
use Maarheeze\Compass\Collector\GitHub\GitHubIssue;

/**
 * @extends Collection<int, GitHubIssue>
 */
class GitHubIssueCollection extends Collection
{
}
