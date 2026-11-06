<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\SiteReports;

use Closure;
use Illuminate\Contracts\Container\Container;
use Illuminate\Http\Client\Factory as HttpFactory;
use Maarheeze\Compass\Collector\CollectorDrivers;
use Maarheeze\Compass\Collector\Drivers\CompassClient\CompassClientSiteReportsCollector;
use Maarheeze\Compass\Collector\Drivers\CompassClient\SiteClient;
use Maarheeze\Compass\Collector\SourceConfig;
use Maarheeze\Compass\Collector\SourceConfigs;

readonly class SiteReportsCollectorManager
{
    /** @var CollectorDrivers<SiteReportsCollector> */
    private CollectorDrivers $collectorDrivers;

    public function __construct(
        Container $container,
        private HttpFactory $http,
        private SourceConfigs $sourceConfigs,
    ) {
        $this->collectorDrivers = new CollectorDrivers($container);
        $this->collectorDrivers->extend(
            'compass-client',
            function (Container $container, SourceConfig $sourceConfig): CompassClientSiteReportsCollector {
                return new CompassClientSiteReportsCollector(new SiteClient(
                    $this->http,
                    $sourceConfig->setting('key'),
                    $sourceConfig->setting('path'),
                ));
            },
        );
    }

    public function collector(string $source): SiteReportsCollector
    {
        return $this->collectorDrivers->collector($this->sourceConfigs->get($source));
    }

    /**
     * @param Closure(Container, SourceConfig): SiteReportsCollector $creator
     */
    public function extend(string $driverName, Closure $creator): void
    {
        $this->collectorDrivers->extend($driverName, $creator);
    }
}
