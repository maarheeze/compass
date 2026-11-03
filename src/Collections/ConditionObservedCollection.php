<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collections;

use Illuminate\Support\Collection;
use Maarheeze\Compass\Alerts\Events\ConditionObserved;

/**
 * @extends Collection<int, ConditionObserved>
 */
class ConditionObservedCollection extends Collection
{
}
