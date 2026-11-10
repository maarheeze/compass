<?php

declare(strict_types=1);

use Illuminate\Pipeline\Pipeline;
use Maarheeze\Compass\Collector\JobLock;
use Maarheeze\Compass\Collector\Sites\Jobs\CollectSites;
use Maarheeze\Uuid\Uuid;

test('middleware releases the job while another job holds its lock', function (): void {
    $collectSites = new CollectSites(Uuid::generate(), fake()->slug())->withFakeQueueInteractions();

    $jobLock = $this->app->make(JobLock::class);
    $jobLock->handle($collectSites, function (CollectSites $lockedCollectSites): void {
        new Pipeline($this->app)
            ->send($lockedCollectSites)
            ->through($lockedCollectSites->middleware())
            ->thenReturn();
    });

    $collectSites->assertReleased();
});
