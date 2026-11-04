<?php

declare(strict_types=1);

use Illuminate\Bus\PendingBatch;
use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Support\Testing\Fakes\BusFake;
use Maarheeze\Compass\Collector\Jobs\FinishRun;
use Maarheeze\Compass\Collector\SiteEndpoint\Jobs\CheckSiteEndpoints;
use Maarheeze\Compass\Collector\TaskType;
use Maarheeze\Compass\Console\CheckSiteEndpointsCommand;
use Maarheeze\Compass\Models\Run;

test('handle starts a run of the site endpoints task', function (): void {
    $this->app->instance(QueueingDispatcher::class, new BusFake($this->app->make(QueueingDispatcher::class)));

    $pendingCommand = $this->artisan(CheckSiteEndpointsCommand::class);
    $pendingCommand->assertSuccessful();

    $this->assertDatabaseHas(Run::class, [
        'source' => null,
        'task' => TaskType::SITE_ENDPOINTS,
    ]);
});

test('handle runs the endpoint checks', function (): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(QueueingDispatcher::class, $busFake);

    $pendingCommand = $this->artisan(CheckSiteEndpointsCommand::class);
    $pendingCommand->run();

    $busFake->assertChained([
        $busFake->chainedBatch(static function (PendingBatch $pendingBatch): bool {
            return $pendingBatch->jobs->sole() instanceof CheckSiteEndpoints;
        }),
        FinishRun::class,
    ]);
});
