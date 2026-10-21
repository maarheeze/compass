<?php

declare(strict_types=1);

use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Support\Testing\Fakes\BusFake;
use Maarheeze\Compass\Collector\GitHub\Jobs\CollectChangedComposerJsons;
use Maarheeze\Compass\Collector\GitHub\Jobs\CollectChangedComposerLocks;
use Maarheeze\Compass\Collector\GitHub\Jobs\CollectRepositories;
use Maarheeze\Compass\Console\SyncGitHubCommand;

test('handle chains the collection of the composer files after the collection of the repositories', function (): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(Dispatcher::class, $busFake);

    $this->artisan(SyncGitHubCommand::class)->assertSuccessful();

    $busFake->assertChained([
        CollectRepositories::class,
        CollectChangedComposerJsons::class,
        CollectChangedComposerLocks::class,
    ]);
});
