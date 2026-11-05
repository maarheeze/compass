<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Sites;

use Closure;
use Illuminate\Contracts\Container\Container;
use InvalidArgumentException;
use Maarheeze\Compass\Collector\Forge\ForgeClientFactory;
use Maarheeze\Compass\Collector\Forge\ForgeSitesCollector;
use Maarheeze\Compass\Collector\SourceConfig;
use Maarheeze\Compass\Collector\SourceConfigs;

use function array_key_exists;

class SitesCollectorManager
{
    /** @var array<string, Closure(Container, SourceConfig): SitesCollector> */
    private array $customCreators = [];

    public function __construct(
        private readonly Container $container,
        private readonly ForgeClientFactory $forgeClientFactory,
        private readonly SourceConfigs $sourceConfigs,
    ) {
    }

    public function collector(string $sourceKey): SitesCollector
    {
        $sourceConfig = $this->sourceConfigs->get($sourceKey);
        $driverName = $sourceConfig->driver();

        if (array_key_exists($driverName, $this->customCreators)) {
            return $this->customCreators[$driverName]($this->container, $sourceConfig);
        }

        return match ($driverName) {
            'forge' => $this->createForgeDriver($sourceConfig),
            default => throw new InvalidArgumentException('unknown sites driver'),
        };
    }

    /**
     * @param Closure(Container, SourceConfig): SitesCollector $creator
     */
    public function extend(string $driverName, Closure $creator): void
    {
        $this->customCreators[$driverName] = $creator;
    }

    private function createForgeDriver(SourceConfig $sourceConfig): ForgeSitesCollector
    {
        return new ForgeSitesCollector(
            $this->forgeClientFactory->make($sourceConfig->setting('token')),
            $sourceConfig->setting('organization'),
        );
    }
}
