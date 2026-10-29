<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Models\Concerns;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Maarheeze\Compass\Models\Observation;

trait HasObservations
{
    public static function bootHasObservations(): void
    {
        static::deleting(static function (self $subject): void {
            $subject->observations()->delete();
        });
    }

    /**
     * @return MorphMany<Observation, $this>
     */
    public function observations(): MorphMany
    {
        return $this->morphMany(Observation::class, 'subject');
    }
}
