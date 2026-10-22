<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collections;

use Illuminate\Database\Eloquent\Collection;
use Maarheeze\Compass\Models\Issue;

/**
 * @extends Collection<array-key, Issue>
 */
class IssueCollection extends Collection
{
}
