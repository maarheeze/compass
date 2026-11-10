<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Broadcasting;

use Maarheeze\Compass\Models\User;

readonly class CompassChannel
{
    public const string NAME = 'compass';

    public function join(User $user): bool
    {
        return true;
    }
}
