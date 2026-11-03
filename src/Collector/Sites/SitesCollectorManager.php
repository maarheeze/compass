<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Sites;

use Closure;
use Illuminate\Config\Repository;
use Illuminate\Contracts\Container\Container;
use InvalidArgumentException;
use Maarheeze\Compass\Collector\Forge\ForgeClientFactory;
use Maarheeze\Compass\Collector\Forge\ForgeSitesCollector;
use Webmozart\Assert\Assert;

use function array_key_exists;

class SitesCollectorManager
{
    /** @var array<string, Closure(Container, array<string, mixed>): SitesCollector> */
    private array $customCreators = [];

    public function __construct(
        private readonly Repository $config,
        private readonly Container $container,
        private readonly ForgeClientFactory $forgeClientFactory,
    ) {
    }

    public function collector(string $sourceKey): SitesCollector
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
            'forge' => $this->createForgeDriver($sourceConfig),
            default => throw new InvalidArgumentException('A source names a driver that has no sites collector.'),
        };
    }

    /**
     * @param Closure(Container, array<string, mixed>): SitesCollector $creator
     */
    public function extend(string $driverName, Closure $creator): void
    {
        $this->customCreators[$driverName] = $creator;
    }

    /**
     * @param array<string, mixed> $sourceConfig
     */
    private function createForgeDriver(array $sourceConfig): ForgeSitesCollector
    {
        Assert::keyExists($sourceConfig, 'organization');
        Assert::keyExists($sourceConfig, 'token');

        return new ForgeSitesCollector(
            $this->forgeClientFactory->make(Assert::stringNotEmpty($sourceConfig['token'])),
            Assert::stringNotEmpty($sourceConfig['organization']),
        );
    }
}
