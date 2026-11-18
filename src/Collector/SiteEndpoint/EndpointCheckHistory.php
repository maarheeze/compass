<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\SiteEndpoint;

use Carbon\CarbonImmutable;
use Illuminate\Database\ConnectionInterface;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\Models\SiteEndpoint;
use Psr\Clock\ClockInterface;
use Throwable;

readonly class EndpointCheckHistory
{
    public function __construct(
        private ClockInterface $clock,
        private ConnectionInterface $connection,
    ) {
    }

    /**
     * @throws Throwable
     */
    public function clear(Site $site, EndpointPath $endpointPath): void
    {
        $this->connection->transaction(function () use ($site, $endpointPath): void {
            $site
                ->endpointChecks()
                ->ofPath($endpointPath)
                ->delete();

            SiteEndpoint::query()
                ->ofSite($site)
                ->ofPath($endpointPath)
                ->update(['due_at' => CarbonImmutable::instance($this->clock->now())]);
        });
    }
}
