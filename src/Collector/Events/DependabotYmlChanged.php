<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Events;

use Illuminate\Queue\SerializesModels;
use Maarheeze\Compass\Models\Repository;

readonly class DependabotYmlChanged
{
    use SerializesModels;

    public function __construct(
        public Repository $repository,
    ) {
    }
}
