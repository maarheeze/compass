<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\SiteDomains;

use Closure;
use Illuminate\Contracts\Container\Container;
use Illuminate\Http\Client\Factory as HttpFactory;
use Maarheeze\Compass\Collector\CollectorDrivers;
use Maarheeze\Compass\Collector\Drivers\Forge\ForgeClient;
use Maarheeze\Compass\Collector\Drivers\Forge\ForgeSiteDomainsCollector;
use Maarheeze\Compass\Collector\SourceConfig;
use Maarheeze\Compass\Collector\SourceConfigs;

readonly class SiteDomainsCollectorManager
{
    /** @var CollectorDrivers<SiteDomainsCollector> */
    private CollectorDrivers $collectorDrivers;

    public function __construct(
        Container $container,
        private HttpFactory $http,
        private SourceConfigs $sourceConfigs,
    ) {
        $this->collectorDrivers = new CollectorDrivers($container);
        $this->collectorDrivers->extend(
            'forge',
            function (Container $container, SourceConfig $sourceConfig): ForgeSiteDomainsCollector {
                return new ForgeSiteDomainsCollector(
                    new ForgeClient($this->http, $sourceConfig->setting('token')),
                    $sourceConfig->setting('organisation'),
                );
            },
        );
    }

    public function collector(string $source): SiteDomainsCollector
    {
        return $this->collectorDrivers->collector($this->sourceConfigs->get($source));
    }

    /**
     * @param Closure(Container, SourceConfig): SiteDomainsCollector $creator
     */
    public function extend(string $driverName, Closure $creator): void
    {
        $this->collectorDrivers->extend($driverName, $creator);
    }
}
