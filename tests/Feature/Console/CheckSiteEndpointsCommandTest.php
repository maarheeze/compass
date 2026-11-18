<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Illuminate\Bus\PendingBatch;
use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Support\Testing\Fakes\BusFake;
use Maarheeze\Compass\Collector\Jobs\FinishRun;
use Maarheeze\Compass\Collector\SiteEndpoint\Jobs\CheckSiteEndpoints;
use Maarheeze\Compass\Collector\TaskType;
use Maarheeze\Compass\Console\CheckSiteEndpointsCommand;
use Maarheeze\Compass\Models\Run;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\Models\SiteEndpoint;
use Maarheeze\Compass\Models\SiteReport;
use Psr\Clock\ClockInterface;
use Symfony\Component\Clock\MockClock;

test('handle starts a run of the site endpoints task when an endpoint is due', function (): void {
    $now = CarbonImmutable::parse(fake()->iso8601());
    $this->app->instance(ClockInterface::class, new MockClock($now));
    SiteEndpoint::factory()
        ->for(SiteReport::factory()->for(Site::factory()->state(['missing_at' => null])))
        ->create(['due_at' => $now->subMinutes(fake()->numberBetween(0, 60))]);
    $this->app->instance(QueueingDispatcher::class, new BusFake($this->app->make(QueueingDispatcher::class)));

    $pendingCommand = $this->artisan(CheckSiteEndpointsCommand::class);
    $pendingCommand->run();

    $this->assertDatabaseHas(Run::class, [
        'source' => null,
        'task' => TaskType::SITE_ENDPOINTS,
    ]);
});

test('handle runs the endpoint checks when an endpoint is due', function (): void {
    $now = CarbonImmutable::parse(fake()->iso8601());
    $this->app->instance(ClockInterface::class, new MockClock($now));
    SiteEndpoint::factory()
        ->for(SiteReport::factory()->for(Site::factory()->state(['missing_at' => null])))
        ->create(['due_at' => $now->subMinutes(fake()->numberBetween(0, 60))]);
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

test('handle starts no run when no endpoint is due', function (): void {
    $now = CarbonImmutable::parse(fake()->iso8601());
    $this->app->instance(ClockInterface::class, new MockClock($now));
    SiteEndpoint::factory()
        ->for(SiteReport::factory()->for(Site::factory()->state(['missing_at' => null])))
        ->create(['due_at' => $now->addMinutes(fake()->numberBetween(1, 60))]);
    $this->app->instance(QueueingDispatcher::class, new BusFake($this->app->make(QueueingDispatcher::class)));

    $pendingCommand = $this->artisan(CheckSiteEndpointsCommand::class);
    $pendingCommand->run();

    $this->assertDatabaseEmpty(Run::class);
});
