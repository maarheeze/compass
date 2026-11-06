<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Sites;

use Closure;
use Illuminate\Contracts\Container\Container;
use Illuminate\Http\Client\Factory as HttpFactory;
use Maarheeze\Compass\Collector\BuiltInDriver;
use Maarheeze\Compass\Collector\CollectorDrivers;
use Maarheeze\Compass\Collector\Drivers\Forge\ForgeClient;
use Maarheeze\Compass\Collector\Drivers\Forge\ForgeSitesCollector;
use Maarheeze\Compass\Collector\SourceConfig;
use Maarheeze\Compass\Collector\SourceConfigs;

readonly class SitesCollectorManager
{
    /** @var CollectorDrivers<SitesCollector> */
    private CollectorDrivers $collectorDrivers;

    public function __construct(
        Container $container,
        private HttpFactory $http,
        private SourceConfigs $sourceConfigs,
    ) {
        $this->collectorDrivers = new CollectorDrivers($container);
        $this->collectorDrivers->extend(
            BuiltInDriver::FORGE,
            function (Container $container, SourceConfig $sourceConfig): ForgeSitesCollector {
                return new ForgeSitesCollector(
                    new ForgeClient($this->http, $sourceConfig->setting('token')),
                    $sourceConfig->setting('organization'),
                );
            },
        );
    }

    public function collector(string $source): SitesCollector
    {
        return $this->collectorDrivers->collector($this->sourceConfigs->get($source));
    }

    /**
     * @param Closure(Container, SourceConfig): SitesCollector $creator
     */
    public function extend(string $driverName, Closure $creator): void
    {
        $this->collectorDrivers->extend($driverName, $creator);
    }
}
