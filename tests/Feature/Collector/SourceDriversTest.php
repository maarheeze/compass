<?php

declare(strict_types=1);

use Illuminate\Contracts\Container\Container;
use Maarheeze\Compass\Collector\Composer\ComposerFilesCollector;
use Maarheeze\Compass\Collector\Sites\SitesCollector;
use Maarheeze\Compass\Collector\SourceConfig;
use Maarheeze\Compass\Collector\SourceDrivers;
use Maarheeze\Compass\Tests\Fixtures\RecordingRepositoryFilesCollector;

test('collector is the driver the creator of the driver of the source makes', function (): void {
    $source = fake()->slug();
    $driverName = fake()->slug();
    $this->setConfiguration('compass.sources', [$source => ['driver' => $driverName]]);
    $recordingRepositoryFilesCollector = new RecordingRepositoryFilesCollector(fake()->text());

    $sourceDrivers = $this->app->make(SourceDrivers::class);
    $sourceDrivers->extend(
        $driverName,
        static function (
            Container $container,
            SourceConfig $sourceConfig,
        ) use ($recordingRepositoryFilesCollector): RecordingRepositoryFilesCollector {
            return $recordingRepositoryFilesCollector;
        },
    );

    expect($sourceDrivers->collector($source, ComposerFilesCollector::class))
        ->toBe($recordingRepositoryFilesCollector);
});

test('collector hands the creator the config of the source', function (): void {
    $source = fake()->slug();
    $driverName = fake()->slug();
    $this->setConfiguration('compass.sources', [$source => ['driver' => $driverName]]);
    $handedSourceConfig = null;

    $sourceDrivers = $this->app->make(SourceDrivers::class);
    $sourceDrivers->extend(
        $driverName,
        static function (
            Container $container,
            SourceConfig $sourceConfig,
        ) use (&$handedSourceConfig): RecordingRepositoryFilesCollector {
            $handedSourceConfig = $sourceConfig;

            return new RecordingRepositoryFilesCollector(fake()->text());
        },
    );
    $sourceDrivers->collector($source, ComposerFilesCollector::class);

    expect($handedSourceConfig?->key)
        ->toBe($source);
});

test('collector asks the creator for a new driver each time', function (): void {
    $source = fake()->slug();
    $driverName = fake()->slug();
    $this->setConfiguration('compass.sources', [$source => ['driver' => $driverName]]);

    $sourceDrivers = $this->app->make(SourceDrivers::class);
    $sourceDrivers->extend(
        $driverName,
        static function (Container $container, SourceConfig $sourceConfig): RecordingRepositoryFilesCollector {
            return new RecordingRepositoryFilesCollector(fake()->text());
        },
    );
    $firstComposerFilesCollector = $sourceDrivers->collector($source, ComposerFilesCollector::class);

    expect($sourceDrivers->collector($source, ComposerFilesCollector::class))
        ->not->toBe($firstComposerFilesCollector);
});

test('collector refuses a key no source is configured under', function (): void {
    $this->setConfiguration('compass.sources', []);

    $sourceDrivers = $this->app->make(SourceDrivers::class);

    $sourceDrivers->collector(fake()->slug(), ComposerFilesCollector::class);
})->throws(InvalidArgumentException::class);

test('collector refuses a source whose driver has no creator', function (): void {
    $source = fake()->slug();
    $this->setConfiguration('compass.sources', [$source => ['driver' => fake()->slug()]]);

    $sourceDrivers = $this->app->make(SourceDrivers::class);

    $sourceDrivers->collector($source, ComposerFilesCollector::class);
})->throws(InvalidArgumentException::class);

test('collector refuses a source whose driver does not implement the collector', function (): void {
    $source = fake()->slug();
    $driverName = fake()->slug();
    $this->setConfiguration('compass.sources', [$source => ['driver' => $driverName]]);

    $sourceDrivers = $this->app->make(SourceDrivers::class);
    $sourceDrivers->extend(
        $driverName,
        static function (Container $container, SourceConfig $sourceConfig): RecordingRepositoryFilesCollector {
            return new RecordingRepositoryFilesCollector(fake()->text());
        },
    );

    $sourceDrivers->collector($source, SitesCollector::class);
})->throws(InvalidArgumentException::class);

test('has collector is true for a source whose driver implements the collector', function (): void {
    $source = fake()->slug();
    $driverName = fake()->slug();
    $this->setConfiguration('compass.sources', [$source => ['driver' => $driverName]]);

    $sourceDrivers = $this->app->make(SourceDrivers::class);
    $sourceDrivers->extend(
        $driverName,
        static function (Container $container, SourceConfig $sourceConfig): RecordingRepositoryFilesCollector {
            return new RecordingRepositoryFilesCollector(fake()->text());
        },
    );
    $hasCollector = $sourceDrivers->hasCollector($source, ComposerFilesCollector::class);

    expect($hasCollector)
        ->toBeTrue();
});

test('has collector is false for a source whose driver does not implement the collector', function (): void {
    $source = fake()->slug();
    $driverName = fake()->slug();
    $this->setConfiguration('compass.sources', [$source => ['driver' => $driverName]]);

    $sourceDrivers = $this->app->make(SourceDrivers::class);
    $sourceDrivers->extend(
        $driverName,
        static function (Container $container, SourceConfig $sourceConfig): RecordingRepositoryFilesCollector {
            return new RecordingRepositoryFilesCollector(fake()->text());
        },
    );
    $hasCollector = $sourceDrivers->hasCollector($source, SitesCollector::class);

    expect($hasCollector)
        ->toBeFalse();
});

test('has collector is false for a source whose driver has no creator', function (): void {
    $source = fake()->slug();
    $this->setConfiguration('compass.sources', [$source => ['driver' => fake()->slug()]]);

    $sourceDrivers = $this->app->make(SourceDrivers::class);
    $hasCollector = $sourceDrivers->hasCollector($source, ComposerFilesCollector::class);

    expect($hasCollector)
        ->toBeFalse();
});
