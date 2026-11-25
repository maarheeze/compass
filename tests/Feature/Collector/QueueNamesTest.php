<?php

declare(strict_types=1);

use Maarheeze\Compass\Collector\QueueNames;
use Maarheeze\Compass\Collector\QueuePriority;

test('for priority gives the configured high queue for the high priority', function (): void {
    $highQueue = fake()->slug();
    $this->setConfiguration('compass.high_queue', $highQueue);

    $queueNames = $this->app->make(QueueNames::class);
    $queueName = $queueNames->forPriority(QueuePriority::HIGH);

    expect($queueName)
        ->toBe($highQueue);
});

test('for priority gives the configured low queue for the low priority', function (): void {
    $lowQueue = fake()->slug();
    $this->setConfiguration('compass.low_queue', $lowQueue);

    $queueNames = $this->app->make(QueueNames::class);
    $queueName = $queueNames->forPriority(QueuePriority::LOW);

    expect($queueName)
        ->toBe($lowQueue);
});

test('for priority gives the default queue for the medium priority', function (): void {
    $this->setConfiguration('compass.high_queue', fake()->unique()->slug());
    $this->setConfiguration('compass.low_queue', fake()->unique()->slug());

    $queueNames = $this->app->make(QueueNames::class);
    $queueName = $queueNames->forPriority(QueuePriority::MEDIUM);

    expect($queueName)
        ->toBeNull();
});
