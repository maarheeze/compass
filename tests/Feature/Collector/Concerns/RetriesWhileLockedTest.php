<?php

declare(strict_types=1);

use Illuminate\Log\Context\Repository as Context;
use Illuminate\Pipeline\Pipeline;
use Maarheeze\Compass\Collector\JobLock;
use Maarheeze\Compass\Collector\RequestBudgetExhausted;
use Maarheeze\Compass\Collector\RunRecorder;
use Maarheeze\Compass\Collector\SiteDomains\Jobs\CollectSiteDomains;
use Maarheeze\Compass\Collector\Sites\Jobs\CollectSites;
use Maarheeze\Compass\Models\Run;
use Maarheeze\Compass\Models\RunResult;
use Maarheeze\Compass\Models\Site;
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

test('middleware releases the job without a result while its request budget is exhausted', function (): void {
    $run = Run::factory()->create();
    $this->app->make(Context::class)->add(RunRecorder::RUN_ID_CONTEXT_KEY, $run->id->toString());
    $secondsUntilRoom = fake()->numberBetween(1, 60);
    $collectSiteDomains = new CollectSiteDomains(Site::factory()->create())->withFakeQueueInteractions();

    new Pipeline($this->app)
        ->send($collectSiteDomains)
        ->through($collectSiteDomains->middleware())
        ->then(static function () use ($secondsUntilRoom): void {
            throw new RequestBudgetExhausted($secondsUntilRoom);
        });

    $collectSiteDomains->assertReleased($secondsUntilRoom);
    $this->assertDatabaseEmpty(RunResult::class);
});
