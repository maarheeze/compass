<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector;

use Illuminate\Config\Repository;
use Illuminate\Contracts\Container\Container;
use Webmozart\Assert\Assert;

class SourceManager
{
    /** @var array<string, SourceDriver>|null */
    private ?array $drivers = null;

    public function __construct(
        private readonly Repository $config,
        private readonly Container $container,
        private readonly SourceDriverRegistry $sourceDriverRegistry,
    ) {
    }

    public function source(string $key): SourceDriver
    {
        if ($this->drivers === null) {
            $this->drivers = $this->makeDrivers();
        }

        Assert::keyExists($this->drivers, $key);

        return $this->drivers[$key];
    }

    /**
     * @param array<string, mixed> $sourceConfig
     */
    private function makeDriver(array $sourceConfig): SourceDriver
    {
        Assert::keyExists($sourceConfig, 'driver');

        $driverClass = $this->sourceDriverRegistry->driverClass(Assert::string($sourceConfig['driver']));

        return Assert::isInstanceOf(
            $this->container->make($driverClass, ['settings' => $driverClass::settings($sourceConfig)]),
            SourceDriver::class,
        );
    }

    /**
     * @return array<string, SourceDriver>
     */
    private function makeDrivers(): array
    {
        $drivers = [];

        foreach ($this->config->array('compass.sources') as $key => $sourceConfig) {
            $drivers[Assert::stringNotEmpty($key)] = $this->makeDriver(Assert::isMap($sourceConfig));
        }

        return $drivers;
    }
}
