<?php

declare(strict_types=1);

use Maarheeze\Compass\Collector\GitHub\GitHubSettings;
use Maarheeze\Compass\Collector\SourceDriverRegistry;
use Maarheeze\Compass\Collector\SourceManager;
use Maarheeze\Compass\Tests\Fakes\FakeSourceDriver;

test('source is the driver its config names, made with the settings of its config', function (): void {
    $key = fake()->slug();
    $driverName = fake()->slug();
    $organization = fake()->slug();
    $token = fake()->sha1();
    $this->app->make(SourceDriverRegistry::class)->register($driverName, FakeSourceDriver::class);
    $this->setConfiguration('compass.sources', [
        $key => [
            'driver' => $driverName,
            'organization' => $organization,
            'token' => $token,
        ],
    ]);

    $sourceManager = $this->app->make(SourceManager::class);
    $source = $sourceManager->source($key);

    expect($source)
        ->toEqual(new FakeSourceDriver(new GitHubSettings($organization, $token)));
});

test('source is the same driver each time it is asked for', function (): void {
    $key = fake()->slug();
    $driverName = fake()->slug();
    $this->app->make(SourceDriverRegistry::class)->register($driverName, FakeSourceDriver::class);
    $this->setConfiguration('compass.sources', [
        $key => [
            'driver' => $driverName,
            'organization' => fake()->slug(),
            'token' => fake()->sha1(),
        ],
    ]);

    $sourceManager = $this->app->make(SourceManager::class);
    $firstSource = $sourceManager->source($key);
    $secondSource = $sourceManager->source($key);

    expect($secondSource)
        ->toBe($firstSource);
});

test('source is a driver of its own for each source', function (): void {
    $firstKey = fake()->unique()->slug();
    $secondKey = fake()->unique()->slug();
    $driverName = fake()->slug();
    $this->app->make(SourceDriverRegistry::class)->register($driverName, FakeSourceDriver::class);
    $this->setConfiguration('compass.sources', [
        $firstKey => [
            'driver' => $driverName,
            'organization' => fake()->slug(),
            'token' => fake()->sha1(),
        ],
        $secondKey => [
            'driver' => $driverName,
            'organization' => fake()->slug(),
            'token' => fake()->sha1(),
        ],
    ]);

    $sourceManager = $this->app->make(SourceManager::class);
    $firstSource = $sourceManager->source($firstKey);
    $secondSource = $sourceManager->source($secondKey);

    expect($secondSource)
        ->not->toBe($firstSource);
});

test('source refuses a key no source is configured under', function (): void {
    $this->setConfiguration('compass.sources', []);

    $sourceManager = $this->app->make(SourceManager::class);

    $sourceManager->source(fake()->slug());
})->throws(InvalidArgumentException::class);

test('source refuses every source while another source names an unregistered driver', function (): void {
    $key = fake()->unique()->slug();
    $driverName = fake()->unique()->slug();
    $this->app->make(SourceDriverRegistry::class)->register($driverName, FakeSourceDriver::class);
    $this->setConfiguration('compass.sources', [
        $key => [
            'driver' => $driverName,
            'organization' => fake()->slug(),
            'token' => fake()->sha1(),
        ],
        fake()->unique()->slug() => [
            'driver' => fake()->unique()->slug(),
        ],
    ]);

    $sourceManager = $this->app->make(SourceManager::class);

    $sourceManager->source($key);
})->throws(InvalidArgumentException::class);
