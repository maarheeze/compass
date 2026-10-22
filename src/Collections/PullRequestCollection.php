<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collections;

use Illuminate\Database\Eloquent\Collection;
use Maarheeze\Compass\Models\PullRequest;

/**
 * @extends Collection<array-key, PullRequest>
 */
class PullRequestCollection extends Collection
{
}
