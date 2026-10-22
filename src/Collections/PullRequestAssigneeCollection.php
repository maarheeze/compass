<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collections;

use Illuminate\Database\Eloquent\Collection;
use Maarheeze\Compass\Models\PullRequestAssignee;

/**
 * @extends Collection<array-key, PullRequestAssignee>
 */
class PullRequestAssigneeCollection extends Collection
{
}
