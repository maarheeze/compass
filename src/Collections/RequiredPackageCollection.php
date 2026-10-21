<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collections;

use Illuminate\Support\Collection;
use Maarheeze\Compass\Collector\Composer\Json\RequiredPackage;

/**
 * @extends Collection<int, RequiredPackage>
 */
class RequiredPackageCollection extends Collection
{
}
