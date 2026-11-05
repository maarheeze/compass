<?php

declare(strict_types=1);

use Illuminate\Contracts\Container\Container;
use Maarheeze\Compass\Collector\CollectorDrivers;
use Maarheeze\Compass\Collector\SourceConfig;

test('collector is the collector the creator of the driver of the source makes', function (): void {
    $driverName = fake()->slug();
    $collector = new stdClass();

    $collectorDrivers = new CollectorDrivers($this->app->make(Container::class));
    $collectorDrivers->extend(
        $driverName,
        static function (Container $container, SourceConfig $sourceConfig) use ($collector): stdClass {
            return $collector;
        },
    );

    expect($collectorDrivers->collector(new SourceConfig(fake()->slug(), ['driver' => $driverName])))
        ->toBe($collector);
});

test('collector hands the creator the config of the source', function (): void {
    $driverName = fake()->slug();
    $sourceConfig = new SourceConfig(fake()->slug(), ['driver' => $driverName]);

    $collectorDrivers = new CollectorDrivers($this->app->make(Container::class));
    $collectorDrivers->extend(
        $driverName,
        static function (Container $container, SourceConfig $handedSourceConfig): SourceConfig {
            return $handedSourceConfig;
        },
    );

    expect($collectorDrivers->collector($sourceConfig))
        ->toBe($sourceConfig);
});

test('collector refuses a source whose driver has no creator', function (): void {
    $collectorDrivers = new CollectorDrivers($this->app->make(Container::class));

    $this->expectException(InvalidArgumentException::class);

    $collectorDrivers->collector(new SourceConfig(fake()->slug(), ['driver' => fake()->slug()]));
});

test('has is true for a driver with a creator', function (): void {
    $driverName = fake()->slug();

    $collectorDrivers = new CollectorDrivers($this->app->make(Container::class));
    $collectorDrivers->extend(
        $driverName,
        static function (Container $container, SourceConfig $sourceConfig): stdClass {
            return new stdClass();
        },
    );

    expect($collectorDrivers->has($driverName))
        ->toBeTrue();
});

test('has is false for a driver without a creator', function (): void {
    $collectorDrivers = new CollectorDrivers($this->app->make(Container::class));

    $hasDriver = $collectorDrivers->has(fake()->slug());

    expect($hasDriver)
        ->toBeFalse();
});
