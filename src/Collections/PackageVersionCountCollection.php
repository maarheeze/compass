<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collections;

use Illuminate\Support\Collection;
use Maarheeze\Compass\Analyzer\Composer\PackageVersionCount;

/**
 * @extends Collection<int, PackageVersionCount>
 */
class PackageVersionCountCollection extends Collection
{
}
