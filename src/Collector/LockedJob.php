<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector;

use Illuminate\Contracts\Queue\ShouldQueue;

interface LockedJob extends ShouldQueue
{
    public function lockKey(): string;
}
