<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\SiteReports;

use Closure;
use Illuminate\Config\Repository;
use Illuminate\Contracts\Container\Container;
use Illuminate\Http\Client\Factory as HttpFactory;
use InvalidArgumentException;
use Maarheeze\Compass\Collector\Client\CompassClientSiteReportsCollector;
use Webmozart\Assert\Assert;

use function array_key_exists;

class SiteReportsCollectorManager
{
    /** @var array<string, Closure(Container, array<string, mixed>): SiteReportsCollector> */
    private array $customCreators = [];

    public function __construct(
        private readonly Repository $config,
        private readonly Container $container,
        private readonly HttpFactory $http,
    ) {
    }

    public function collector(string $sourceKey): SiteReportsCollector
    {
        $sourceConfigs = $this->config->array('compass.sources');

        Assert::keyExists($sourceConfigs, $sourceKey);
        $sourceConfig = Assert::isMap($sourceConfigs[$sourceKey]);

        Assert::keyExists($sourceConfig, 'driver');
        $driverName = Assert::stringNotEmpty($sourceConfig['driver']);

        if (array_key_exists($driverName, $this->customCreators)) {
            return $this->customCreators[$driverName]($this->container, $sourceConfig);
        }

        return match ($driverName) {
            'compass-client' => $this->createCompassClientDriver($sourceConfig),
            default => throw new InvalidArgumentException('A source names a driver that has no site reports collector.'),
        };
    }

    /**
     * @param Closure(Container, array<string, mixed>): SiteReportsCollector $creator
     */
    public function extend(string $driverName, Closure $creator): void
    {
        $this->customCreators[$driverName] = $creator;
    }

    /**
     * @param array<string, mixed> $sourceConfig
     */
    private function createCompassClientDriver(array $sourceConfig): CompassClientSiteReportsCollector
    {
        Assert::keyExists($sourceConfig, 'key');
        Assert::keyExists($sourceConfig, 'path');

        return new CompassClientSiteReportsCollector(
            $this->http,
            Assert::stringNotEmpty($sourceConfig['key']),
            Assert::stringNotEmpty($sourceConfig['path']),
        );
    }
}
