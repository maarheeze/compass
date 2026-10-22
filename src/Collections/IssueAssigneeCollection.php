<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collections;

use Illuminate\Database\Eloquent\Collection;
use Maarheeze\Compass\Models\IssueAssignee;

/**
 * @extends Collection<array-key, IssueAssignee>
 */
class IssueAssigneeCollection extends Collection
{
}
