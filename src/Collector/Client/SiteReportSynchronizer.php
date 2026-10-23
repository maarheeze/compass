<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Client;

use Carbon\CarbonImmutable;
use Illuminate\Database\ConnectionInterface;
use Maarheeze\Compass\Models\Site;
use Throwable;

readonly class SiteReportSynchronizer
{
    public function __construct(
        private ConnectionInterface $connection,
    ) {
    }

    /**
     * @throws Throwable
     */
    public function clearReport(Site $site): void
    {
        $this->connection->transaction(static function () use ($site): void {
            $site->update([
                'client_status' => ClientStatus::NOT_INSTALLED,
                'app_debug' => null,
                'app_env' => null,
                'app_name' => null,
                'client_collected_at' => CarbonImmutable::now(),
            ]);

            $site->endpoints()->delete();
        });
    }

    public function markUnauthorized(Site $site): void
    {
        $site->update([
            'client_status' => ClientStatus::UNAUTHORIZED,
            'client_collected_at' => CarbonImmutable::now(),
        ]);
    }

    public function markUnreachable(Site $site): void
    {
        $site->update([
            'client_status' => ClientStatus::UNREACHABLE,
            'client_collected_at' => CarbonImmutable::now(),
        ]);
    }

    /**
     * @throws Throwable
     */
    public function storeReport(Site $site, SiteReport $siteReport): void
    {
        $this->connection->transaction(static function () use ($site, $siteReport): void {
            $site->update([
                'client_status' => ClientStatus::INSTALLED,
                'app_debug' => $siteReport->appDebug,
                'app_env' => $siteReport->appEnv,
                'app_name' => $siteReport->appName,
                'client_collected_at' => CarbonImmutable::now(),
            ]);

            $site->endpoints()->delete();

            foreach ($siteReport->endpoints as $endpoint) {
                $site->endpoints()->create(['path' => $endpoint]);
            }
        });
    }
}
