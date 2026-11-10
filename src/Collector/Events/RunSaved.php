<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Events;

use Illuminate\Queue\SerializesModels;
use Maarheeze\Compass\Models\Run;

readonly class RunSaved
{
    use SerializesModels;

    public function __construct(
        public Run $run,
    ) {
    }
}
