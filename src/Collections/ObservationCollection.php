<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collections;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Maarheeze\Compass\Models\Observation;

/**
 * @extends Collection<array-key, Observation>
 */
class ObservationCollection extends Collection
{
    public function lastObservedAt(): ?CarbonImmutable
    {
        return $this
            ->sortByDesc('observed_at')
            ->first()
            ?->observed_at;
    }
}
