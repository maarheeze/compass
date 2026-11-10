<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts\Events;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Queue\SerializesModels;
use Maarheeze\Compass\Models\AlertHistoryEntry;

readonly class AlertHistoryEntryCreated implements ShouldDispatchAfterCommit
{
    use SerializesModels;

    public function __construct(
        public AlertHistoryEntry $alertHistoryEntry,
    ) {
    }
}
