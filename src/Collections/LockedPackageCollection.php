<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collections;

use Illuminate\Support\Collection;
use Maarheeze\Compass\Collector\Composer\Lock\LockedPackage;

/**
 * @extends Collection<int, LockedPackage>
 */
class LockedPackageCollection extends Collection
{
}
