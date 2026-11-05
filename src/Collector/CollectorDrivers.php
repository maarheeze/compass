<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector;

use Closure;
use Illuminate\Contracts\Container\Container;
use InvalidArgumentException;

use function array_key_exists;

/**
 * @template TCollector of object
 */
class CollectorDrivers
{
    /** @var array<string, Closure(Container, SourceConfig): TCollector> */
    private array $creators = [];

    public function __construct(
        private readonly Container $container,
    ) {
    }

    /**
     * @return TCollector
     */
    public function collector(SourceConfig $sourceConfig): object
    {
        $driverName = $sourceConfig->driver();

        if (!$this->has($driverName)) {
            throw new InvalidArgumentException('unknown driver');
        }

        return $this->creators[$driverName]($this->container, $sourceConfig);
    }

    /**
     * @param Closure(Container, SourceConfig): TCollector $creator
     */
    public function extend(string $driverName, Closure $creator): void
    {
        $this->creators[$driverName] = $creator;
    }

    public function has(string $driverName): bool
    {
        return array_key_exists($driverName, $this->creators);
    }
}
