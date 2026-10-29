<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Models\Concerns;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Maarheeze\Compass\Models\Alert;

trait HasAlerts
{
    /**
     * @return MorphMany<Alert, $this>
     */
    public function alerts(): MorphMany
    {
        return $this->morphMany(Alert::class, 'subject');
    }
}
