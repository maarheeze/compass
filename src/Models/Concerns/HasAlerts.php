<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Models\Concerns;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Maarheeze\Compass\Models\Alert;

trait HasAlerts
{
    public static function bootHasAlerts(): void
    {
        static::deleting(static function (self $subject): void {
            $subject->alerts()->delete();
        });
    }

    /**
     * @return MorphMany<Alert, $this>
     */
    public function alerts(): MorphMany
    {
        return $this->morphMany(Alert::class, 'subject');
    }
}
