<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Client;

use Illuminate\Http\Client\Factory as HttpFactory;
use Maarheeze\Compass\Collector\CollectingFailed;
use Maarheeze\Compass\Collector\SiteReports\CollectedSiteReport;
use Maarheeze\Compass\Collector\SiteReports\SiteReportsCollector;
use Maarheeze\Compass\Models\Site;

readonly class CompassClientSiteReportsCollector implements SiteReportsCollector
{
    public function __construct(
        private HttpFactory $http,
        private string $key,
        private string $path,
    ) {
    }

    /**
     * @throws CollectingFailed
     */
    public function siteReport(Site $site): CollectedSiteReport
    {
        $siteQuery = new SiteQuery(new ClientKey($this->key), new ClientUrlFactory($this->path), $this->http);

        try {
            $siteReport = $siteQuery->report($site);
        } catch (ClientIncompatible) {
            return CollectedSiteReport::incompatible();
        } catch (ClientNotInstalled) {
            return CollectedSiteReport::notInstalled();
        } catch (ClientUnauthorized) {
            return CollectedSiteReport::unauthorized();
        } catch (ClientUnreachable $clientUnreachable) {
            throw CollectingFailed::fromThrowable($clientUnreachable);
        }

        return CollectedSiteReport::installed(
            $siteReport->appDebug,
            $siteReport->appEnv,
            $siteReport->appName,
            $siteReport->endpoints,
        );
    }
}
