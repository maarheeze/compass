<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\SiteReports;

use Closure;
use Illuminate\Contracts\Container\Container;
use Illuminate\Http\Client\Factory as HttpFactory;
use InvalidArgumentException;
use Maarheeze\Compass\Collector\Client\CompassClientSiteReportsCollector;
use Maarheeze\Compass\Collector\SourceConfig;
use Maarheeze\Compass\Collector\SourceConfigs;

use function array_key_exists;

class SiteReportsCollectorManager
{
    /** @var array<string, Closure(Container, SourceConfig): SiteReportsCollector> */
    private array $customCreators = [];

    public function __construct(
        private readonly Container $container,
        private readonly HttpFactory $http,
        private readonly SourceConfigs $sourceConfigs,
    ) {
    }

    public function collector(string $sourceKey): SiteReportsCollector
    {
        $sourceConfig = $this->sourceConfigs->get($sourceKey);
        $driverName = $sourceConfig->driver();

        if (array_key_exists($driverName, $this->customCreators)) {
            return $this->customCreators[$driverName]($this->container, $sourceConfig);
        }

        return match ($driverName) {
            'compass-client' => $this->createCompassClientDriver($sourceConfig),
            default => throw new InvalidArgumentException('unknown site reports driver'),
        };
    }

    /**
     * @param Closure(Container, SourceConfig): SiteReportsCollector $creator
     */
    public function extend(string $driverName, Closure $creator): void
    {
        $this->customCreators[$driverName] = $creator;
    }

    private function createCompassClientDriver(SourceConfig $sourceConfig): CompassClientSiteReportsCollector
    {
        return new CompassClientSiteReportsCollector(
            $this->http,
            $sourceConfig->setting('key'),
            $sourceConfig->setting('path'),
        );
    }
}
