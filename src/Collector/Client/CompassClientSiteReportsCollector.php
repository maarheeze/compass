<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Client;

use Illuminate\Http\Client\Factory as HttpFactory;
use Maarheeze\Compass\Collector\CollectingFailed;
use Maarheeze\Compass\Collector\SiteReports\CollectedSiteReport;
use Maarheeze\Compass\Collector\SiteReports\SiteReportsCollector;
use Maarheeze\Compass\Models\SiteReport;

readonly class CompassClientSiteReportsCollector implements SiteReportsCollector
{
    public function __construct(
        private HttpFactory $http,
        private string $key,
        private string $path,
    ) {
    }

    public function getKey(SiteReport $siteReport): string
    {
        return new ClientKey($this->key)->forSiteReport($siteReport);
    }

    public function getPath(SiteReport $siteReport): string
    {
        return new ClientUrlFactory($this->path)->path($siteReport);
    }

    /**
     * @throws CollectingFailed
     */
    public function siteReport(SiteReport $siteReport): CollectedSiteReport
    {
        $siteQuery = new SiteQuery(new ClientKey($this->key), new ClientUrlFactory($this->path), $this->http);

        try {
            $clientReport = $siteQuery->report($siteReport);
        } catch (ClientIncompatible) {
            return CollectedSiteReport::incompatible();
        } catch (ClientNotInstalled) {
            return CollectedSiteReport::notInstalled();
        } catch (ClientUnauthorized) {
            return CollectedSiteReport::unauthorized();
        } catch (ClientRequestFailed $clientRequestFailed) {
            throw CollectingFailed::fromThrowable($clientRequestFailed);
        }

        return CollectedSiteReport::installed(
            $clientReport->appDebug,
            $clientReport->appEnv,
            $clientReport->appName,
            $clientReport->endpoints,
        );
    }
}
