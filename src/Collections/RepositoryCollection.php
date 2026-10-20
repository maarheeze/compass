<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collections;

use Illuminate\Database\Eloquent\Collection;
use Maarheeze\Compass\Models\Repository;

/**
 * @extends Collection<array-key, Repository>
 */
class RepositoryCollection extends Collection
{
}
