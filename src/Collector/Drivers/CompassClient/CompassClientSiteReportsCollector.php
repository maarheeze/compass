<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Drivers\CompassClient;

use Maarheeze\Compass\Collector\CollectingFailed;
use Maarheeze\Compass\Collector\SiteReports\CollectedSiteReport;
use Maarheeze\Compass\Collector\SiteReports\SiteReportsCollector;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\Models\SiteReport;
use Random\Randomizer;

use function bin2hex;

readonly class CompassClientSiteReportsCollector implements SiteReportsCollector
{
    public function __construct(
        private Randomizer $randomizer,
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
            $clientReport = $siteQuery->report($siteReport, $this->siteKey($siteReport->site));
        } catch (ClientIncompatible) {
            return CollectedSiteReport::incompatible();
        } catch (ClientNotInstalled) {
            return CollectedSiteReport::notInstalled();
        } catch (ClientUnauthorised) {
            return CollectedSiteReport::unauthorised();
        } catch (ClientRequestFailed $clientRequestFailed) {
            throw CollectingFailed::fromThrowable($clientRequestFailed);
        }

        return CollectedSiteReport::installed(
            $clientReport->appDebug,
            $clientReport->appEnv,
            $clientReport->appName,
            $clientReport->endpoints,
            $clientReport->maintenanceMode,
        );
    }

    private function siteKey(Site $site): string
    {
        if ($site->key !== null) {
            return $site->key;
        }

        $siteKey = bin2hex($this->randomizer->getBytes(32));
        $site->update(['key' => $siteKey]);

        return $siteKey;
    }
}
