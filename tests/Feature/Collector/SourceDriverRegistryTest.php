<?php

declare(strict_types=1);

use Maarheeze\Compass\Collector\SourceDriverRegistry;
use Maarheeze\Compass\Tests\Fakes\FakeSourceDriver;

test('driver class is the class registered under the name', function (): void {
    $name = fake()->slug();

    $sourceDriverRegistry = $this->app->make(SourceDriverRegistry::class);
    $sourceDriverRegistry->register($name, FakeSourceDriver::class);
    $driverClass = $sourceDriverRegistry->driverClass($name);

    expect($driverClass)
        ->toBe(FakeSourceDriver::class);
});

test('driver class refuses a name no driver is registered under', function (): void {
    $sourceDriverRegistry = $this->app->make(SourceDriverRegistry::class);

    $sourceDriverRegistry->driverClass(fake()->slug());
})->throws(InvalidArgumentException::class);

test('register refuses a class that is not a source driver', function (): void {
    $sourceDriverRegistry = $this->app->make(SourceDriverRegistry::class);

    $sourceDriverRegistry->register(fake()->slug(), stdClass::class);
})->throws(InvalidArgumentException::class);
