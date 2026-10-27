<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector;

use Maarheeze\Compass\Models\Run;

readonly class SyncStatus
{
    public function isRunning(): bool
    {
        return Run::query()
            ->where('status', RunStatus::RUNNING)
            ->exists();
    }
}
