<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collections;

use Illuminate\Support\Collection;
use Maarheeze\Compass\Collector\Repositories\CollectedRepository;

/**
 * @extends Collection<int, CollectedRepository>
 */
class CollectedRepositoryCollection extends Collection
{
}
