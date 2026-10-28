<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Illuminate\Bus\PendingBatch;
use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Support\Testing\Fakes\BusFake;
use Livewire\LivewireManager;
use Maarheeze\Compass\Collector\Forge\Jobs\CollectSites;
use Maarheeze\Compass\Collector\GitHub\Jobs\CollectRepositories;
use Maarheeze\Compass\Collector\Jobs\FinishRun;
use Maarheeze\Compass\Collector\RunStatus;
use Maarheeze\Compass\Collector\RunType;
use Maarheeze\Compass\Collector\SiteEndpoint\Jobs\CheckSiteEndpoints;
use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Livewire\SyncButton;
use Maarheeze\Compass\Models\Repository;
use Maarheeze\Compass\Models\Run;

test('sync starts a collection of the repositories', function (): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(QueueingDispatcher::class, $busFake);

    $this->app
        ->make(LivewireManager::class)
        ->test(SyncButton::class)
        ->call('sync');

    $busFake->assertChained([
        $busFake->chainedBatch(static function (PendingBatch $pendingBatch): bool {
            return $pendingBatch->jobs->sole() instanceof CollectRepositories;
        }),
        FinishRun::class,
    ]);
});

test('sync starts a collection of the sites', function (): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(QueueingDispatcher::class, $busFake);

    $this->app
        ->make(LivewireManager::class)
        ->test(SyncButton::class)
        ->call('sync');

    $busFake->assertChained([
        $busFake->chainedBatch(static function (PendingBatch $pendingBatch): bool {
            return $pendingBatch->jobs->sole() instanceof CollectSites;
        }),
        FinishRun::class,
    ]);
});

test('sync starts a check of the site endpoints', function (): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(QueueingDispatcher::class, $busFake);

    $this->app
        ->make(LivewireManager::class)
        ->test(SyncButton::class)
        ->call('sync');

    $busFake->assertChained([
        $busFake->chainedBatch(static function (PendingBatch $pendingBatch): bool {
            return $pendingBatch->jobs->sole() instanceof CheckSiteEndpoints;
        }),
        FinishRun::class,
    ]);
});

test('render tells the view whether a sync is running', function (): void {
    Run::factory()->create([
        'type' => fake()->randomElement(RunType::cases()),
        'status' => RunStatus::RUNNING,
    ]);

    $syncButton = $this->app
        ->make(LivewireManager::class)
        ->test(SyncButton::class);

    $syncButton->assertViewHas('isSyncing', true);
});

test('render hands the view when the last collection was collected', function (): void {
    $repositoryCollectedAt = CarbonImmutable::instance(fake()->dateTime());
    Repository::factory()->create(['collected_at' => $repositoryCollectedAt]);

    $syncButton = $this->app
        ->make(LivewireManager::class)
        ->test(SyncButton::class);

    $syncButton->assertViewHas(
        'lastCollectedAt',
        static function (?CarbonImmutable $lastCollectedAt) use ($repositoryCollectedAt): bool {
            return $lastCollectedAt?->getTimestamp() === $repositoryCollectedAt->getTimestamp();
        },
    );
});

test('render hands the view the url of the runs', function (): void {
    $syncButton = $this->app
        ->make(LivewireManager::class)
        ->test(SyncButton::class);

    $syncButton->assertViewHas('runsUrl', route(RouteName::RUNS));
});
