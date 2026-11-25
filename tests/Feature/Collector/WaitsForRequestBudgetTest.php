<?php

declare(strict_types=1);

use Maarheeze\Compass\Collector\RequestBudgetExhausted;
use Maarheeze\Compass\Collector\Sites\Jobs\CollectSites;
use Maarheeze\Compass\Collector\WaitsForRequestBudget;
use Maarheeze\Uuid\Uuid;

test('handle releases the job for the seconds until its request budget has room', function (): void {
    $secondsUntilRoom = fake()->numberBetween(1, 60);
    $collectSites = new CollectSites(Uuid::generate(), fake()->slug())->withFakeQueueInteractions();

    $waitsForRequestBudget = $this->app->make(WaitsForRequestBudget::class);
    $waitsForRequestBudget->handle($collectSites, static function () use ($secondsUntilRoom): void {
        throw new RequestBudgetExhausted($secondsUntilRoom);
    });

    $collectSites->assertReleased($secondsUntilRoom);
});

test('handle runs the job', function (): void {
    $collectSites = new CollectSites(Uuid::generate(), fake()->slug());
    $ranCollectSites = null;

    $waitsForRequestBudget = $this->app->make(WaitsForRequestBudget::class);
    $waitsForRequestBudget->handle(
        $collectSites,
        static function (CollectSites $handledCollectSites) use (&$ranCollectSites): void {
            $ranCollectSites = $handledCollectSites;
        },
    );

    expect($ranCollectSites)
        ->toBe($collectSites);
});

test('handle passes any other exception on', function (): void {
    $collectSites = new CollectSites(Uuid::generate(), fake()->slug());

    $waitsForRequestBudget = $this->app->make(WaitsForRequestBudget::class);
    $waitsForRequestBudget->handle($collectSites, static function (): void {
        throw new RuntimeException(fake()->sentence());
    });
})->throws(RuntimeException::class);
