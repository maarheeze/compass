<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Models\Concerns;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Maarheeze\Compass\Models\Alert;
use Maarheeze\Compass\Models\Builders\AlertBuilder;
use Maarheeze\Compass\Models\Scopes\RegisteredAlertTypes;

trait HasAlerts
{
    public static function bootHasAlerts(): void
    {
        static::deleting(static function (self $subject): void {
            $subject
                ->alerts()
                ->withoutGlobalScope(RegisteredAlertTypes::class)
                ->delete();
        });
    }

    /**
     * @return MorphMany<Alert, $this>
     */
    public function alerts(): MorphMany
    {
        return $this->morphMany(Alert::class, 'subject');
    }

    public function alertsIncludingChildren(): AlertBuilder
    {
        return Alert::query()->ofSubject($this);
    }
}
