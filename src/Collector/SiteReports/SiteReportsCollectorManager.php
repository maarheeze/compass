<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\SiteReports;

use Closure;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Http\Client\Factory as HttpFactory;
use Maarheeze\Compass\Collector\CollectorDrivers;
use Maarheeze\Compass\Collector\CollectorSchedule;
use Maarheeze\Compass\Collector\Drivers\CompassClient\CompassClientSiteReportsCollector;
use Maarheeze\Compass\Collector\Drivers\CompassClient\SiteClient;
use Maarheeze\Compass\Collector\SourceConfig;
use Maarheeze\Compass\Collector\SourceConfigs;
use Random\Randomizer;

readonly class SiteReportsCollectorManager
{
    /** @var CollectorDrivers<SiteReportsCollector> */
    private CollectorDrivers $collectorDrivers;

    public function __construct(
        private CollectorSchedule $collectorSchedule,
        Container $container,
        private HttpFactory $http,
        private Randomizer $randomizer,
        private SourceConfigs $sourceConfigs,
        private UrlGenerator $urlGenerator,
    ) {
        $this->collectorDrivers = new CollectorDrivers($container);
        $this->collectorDrivers->extend(
            'compass-client',
            function (Container $container, SourceConfig $sourceConfig): CompassClientSiteReportsCollector {
                return new CompassClientSiteReportsCollector(
                    $this->randomizer,
                    new SiteClient(
                        $this->collectorSchedule,
                        $this->http,
                        $sourceConfig->setting('path'),
                        $sourceConfig->key,
                        $this->urlGenerator,
                    ),
                );
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
