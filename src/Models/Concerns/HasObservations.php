<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Models\Concerns;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Maarheeze\Compass\Models\Observation;

trait HasObservations
{
    /**
     * @return MorphMany<Observation, $this>
     */
    public function observations(): MorphMany
    {
        return $this->morphMany(Observation::class, 'subject');
    }
}
