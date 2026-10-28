<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts\Events;

use Illuminate\Queue\SerializesModels;
use Maarheeze\Compass\Models\Alert;

readonly class AlertRecurred
{
    use SerializesModels;

    public function __construct(
        public Alert $alert,
    ) {
    }
}
