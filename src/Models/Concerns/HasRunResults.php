<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Models\Concerns;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Maarheeze\Compass\Models\RunResult;

trait HasRunResults
{
    public static function bootHasRunResults(): void
    {
        static::deleting(static function (self $subject): void {
            $subject
                ->runResults()
                ->delete();
        });
    }

    /**
     * @return MorphMany<RunResult, $this>
     */
    public function runResults(): MorphMany
    {
        return $this->morphMany(RunResult::class, 'subject');
    }
}
