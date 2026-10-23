<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Client;

readonly class SiteReport
{
    /**
     * @param list<string> $endpoints
     */
    public function __construct(
        public bool $appDebug,
        public string $appEnv,
        public string $appName,
        public array $endpoints,
    ) {
    }
}
