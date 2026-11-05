<?php

declare(strict_types=1);

use Maarheeze\Compass\Collector\SourceConfig;
use Maarheeze\Compass\Collector\SourceConfigs;

test('get is the config of the source under the key', function (): void {
    $source = fake()->slug();
    $driverName = fake()->slug();
    $this->setConfiguration('compass.sources', [
        $source => [
            'driver' => $driverName,
        ],
    ]);

    $sourceConfigs = $this->app->make(SourceConfigs::class);
    $sourceConfig = $sourceConfigs->get($source);

    expect($sourceConfig->driver())
        ->toBe($driverName);
});

test('get refuses a key no source is configured under', function (): void {
    $this->setConfiguration('compass.sources', []);

    $sourceConfigs = $this->app->make(SourceConfigs::class);

    $this->expectException(InvalidArgumentException::class);

    $sourceConfigs->get(fake()->slug());
});

test('all is the config of every source, by its key', function (): void {
    $sources = [fake()->unique()->slug(), fake()->unique()->slug()];
    $this->setConfiguration('compass.sources', [
        $sources[0] => [
            'driver' => fake()->slug(),
        ],
        $sources[1] => [
            'driver' => fake()->slug(),
        ],
    ]);

    $sourceConfigs = $this->app->make(SourceConfigs::class);
    $allSourceConfigs = $sourceConfigs->all();

    $allSources = array_map(static function (SourceConfig $sourceConfig): string {
        return $sourceConfig->key;
    }, $allSourceConfigs);
    expect($allSources)
        ->toBe($sources);
});
