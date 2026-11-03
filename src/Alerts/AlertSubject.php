<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Maarheeze\Compass\Models\Alert;

interface AlertSubject
{
    /**
     * @return MorphMany<Alert, covariant Model>
     */
    public function alerts(): MorphMany;

    public function displayName(): string;
}
