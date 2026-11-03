<?php

declare(strict_types=1);

use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Support\Testing\Fakes\BusFake;
use Maarheeze\Compass\Collector\CollectionType;
use Maarheeze\Compass\Console\CollectCommand;
use Maarheeze\Compass\Models\Run;

test('handle starts a run of the collection of the source', function (): void {
    $this->app->instance(QueueingDispatcher::class, new BusFake($this->app->make(QueueingDispatcher::class)));
    $collectionType = fake()->randomElement(CollectionType::cases());

    $pendingCommand = $this->artisan(CollectCommand::class, [
        'source' => 'github',
        'collection' => $collectionType->value,
    ]);
    $pendingCommand->assertSuccessful();

    $this->assertDatabaseHas(Run::class, [
        'source' => 'github',
        'collection' => $collectionType,
    ]);
});

test('handle refuses a collection that does not exist', function (): void {
    $this->app->instance(QueueingDispatcher::class, new BusFake($this->app->make(QueueingDispatcher::class)));

    $this->expectException(ValueError::class);

    $pendingCommand = $this->artisan(CollectCommand::class, [
        'source' => 'github',
        'collection' => fake()->slug(),
    ]);
    $pendingCommand->run();
});

test('handle refuses a source that is not configured', function (): void {
    $this->app->instance(QueueingDispatcher::class, new BusFake($this->app->make(QueueingDispatcher::class)));

    $this->expectException(InvalidArgumentException::class);

    $pendingCommand = $this->artisan(CollectCommand::class, [
        'source' => fake()->slug(),
        'collection' => fake()->randomElement(CollectionType::cases())->value,
    ]);
    $pendingCommand->run();
});
