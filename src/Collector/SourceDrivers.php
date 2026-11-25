<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector;

use Closure;
use Illuminate\Contracts\Container\Container;
use InvalidArgumentException;
use Webmozart\Assert\Assert;

use function array_key_exists;

class SourceDrivers
{
    /** @var array<string, Closure(Container, SourceConfig): object> */
    private array $creators = [];

    public function __construct(
        private readonly Container $container,
        private readonly SourceConfigs $sourceConfigs,
    ) {
    }

    /**
     * @template TCollector of object
     *
     * @param class-string<TCollector> $collectorContract
     *
     * @return TCollector
     */
    public function collector(string $source, string $collectorContract): object
    {
        return Assert::isInstanceOf($this->driver($this->sourceConfigs->get($source)), $collectorContract);
    }

    /**
     * @param Closure(Container, SourceConfig): object $creator
     */
    public function extend(string $driverName, Closure $creator): void
    {
        $this->creators[$driverName] = $creator;
    }

    /**
     * @param class-string $collectorContract
     */
    public function hasCollector(string $source, string $collectorContract): bool
    {
        $sourceConfig = $this->sourceConfigs->get($source);

        if (!array_key_exists($sourceConfig->driver(), $this->creators)) {
            return false;
        }

        return $this->driver($sourceConfig) instanceof $collectorContract;
    }

    private function driver(SourceConfig $sourceConfig): object
    {
        $driverName = $sourceConfig->driver();

        if (!array_key_exists($driverName, $this->creators)) {
            throw new InvalidArgumentException('unknown driver');
        }

        return $this->creators[$driverName]($this->container, $sourceConfig);
    }
}
