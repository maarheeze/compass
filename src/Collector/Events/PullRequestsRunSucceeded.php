<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Events;

use Maarheeze\Uuid\Uuid;

readonly class PullRequestsRunSucceeded implements RunSucceeded
{
    public function __construct(
        public Uuid $runId,
    ) {
    }
}
