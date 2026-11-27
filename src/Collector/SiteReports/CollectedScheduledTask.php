<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\SiteReports;

readonly class CollectedScheduledTask
{
    public function __construct(
        public string $command,
        public string $expression,
    ) {
    }
}
