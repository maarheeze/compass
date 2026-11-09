<?php

declare(strict_types=1);

use Maarheeze\Compass\Collector\JobLock;
use Maarheeze\Compass\Collector\Sites\Jobs\CollectSites;
use Maarheeze\Uuid\Uuid;

test('middleware locks the job with the job lock', function (): void {
    $collectSites = new CollectSites(Uuid::generate(), fake()->slug());

    $middleware = $collectSites->middleware();

    expect($middleware)
        ->toBe([JobLock::class]);
});
