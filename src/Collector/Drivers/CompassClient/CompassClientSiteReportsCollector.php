<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Drivers\CompassClient;

use Maarheeze\Compass\Collector\CollectingFailed;
use Maarheeze\Compass\Collector\SiteReports\CollectedSiteReport;
use Maarheeze\Compass\Collector\SiteReports\SiteReportsCollector;
use Maarheeze\Compass\Models\SiteReport;

readonly class CompassClientSiteReportsCollector implements SiteReportsCollector
{
    public function __construct(
        private SiteClient $siteClient,
    ) {
    }

    /**
     * @throws CollectingFailed
     */
    public function siteReport(SiteReport $siteReport): CollectedSiteReport
    {
        $siteQuery = new SiteQuery($this->siteClient);

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
