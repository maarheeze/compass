<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collections;

use Illuminate\Database\Eloquent\Collection;
use Maarheeze\Compass\Models\ComposerLock;

/**
 * @extends Collection<array-key, ComposerLock>
 */
class ComposerLockCollection extends Collection
{
}
