<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collections;

use Illuminate\Support\Collection;
use Maarheeze\Compass\Console\ScheduledCommand;

/**
 * @extends Collection<int, ScheduledCommand>
 */
class ScheduledCommandCollection extends Collection
{
}
