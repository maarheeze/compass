<?php

declare(strict_types=1);

use Maarheeze\Compass\Collector\SourceConfig;
use Maarheeze\Compass\Collector\SourceConfigs;

test('get is the config of the source under the key', function (): void {
    $sourceKey = fake()->slug();
    $driverName = fake()->slug();
    $this->setConfiguration('compass.sources', [
        $sourceKey => [
            'driver' => $driverName,
        ],
    ]);

    $sourceConfigs = $this->app->make(SourceConfigs::class);
    $sourceConfig = $sourceConfigs->get($sourceKey);

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
    $sourceKeys = [fake()->unique()->slug(), fake()->unique()->slug()];
    $this->setConfiguration('compass.sources', [
        $sourceKeys[0] => [
            'driver' => fake()->slug(),
        ],
        $sourceKeys[1] => [
            'driver' => fake()->slug(),
        ],
    ]);

    $sourceConfigs = $this->app->make(SourceConfigs::class);
    $allSourceConfigs = $sourceConfigs->all();

    $allSourceKeys = array_map(static function (SourceConfig $sourceConfig): string {
        return $sourceConfig->key;
    }, $allSourceConfigs);
    expect($allSourceKeys)
        ->toBe($sourceKeys);
});
