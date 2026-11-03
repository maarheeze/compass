<?php

declare(strict_types=1);

use Illuminate\Bus\ChainedBatch;
use Illuminate\Bus\PendingBatch;
use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Support\Testing\Fakes\BusFake;
use Maarheeze\Compass\Collector\CollectionStarter;
use Maarheeze\Compass\Collector\CollectionType;
use Maarheeze\Compass\Collector\Events\ForgeRunSucceeded;
use Maarheeze\Compass\Collector\Events\GitHubRunSucceeded;
use Maarheeze\Compass\Collector\Events\IssuesRunSucceeded;
use Maarheeze\Compass\Collector\Events\PullRequestsRunSucceeded;
use Maarheeze\Compass\Collector\Forge\Jobs\CollectSites;
use Maarheeze\Compass\Collector\GitHub\GitHubDriver;
use Maarheeze\Compass\Collector\GitHub\Jobs\CollectIssues;
use Maarheeze\Compass\Collector\GitHub\Jobs\CollectPullRequests;
use Maarheeze\Compass\Collector\GitHub\Jobs\CollectRepositories;
use Maarheeze\Compass\Collector\Jobs\FinishRun;
use Maarheeze\Compass\Collector\Source;
use Maarheeze\Compass\Models\Run;

test('start runs the first job of the collection', function (
    CollectionType $collectionType,
    string $firstJobClass,
): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(QueueingDispatcher::class, $busFake);

    $source = new Source(fake()->slug(), new GitHubDriver(fake()->slug(), fake()->sha1()));

    $collectionStarter = $this->app->make(CollectionStarter::class);
    $collectionStarter->start($source, $collectionType);

    $busFake->assertChained([
        $busFake->chainedBatch(static function (PendingBatch $pendingBatch) use ($firstJobClass): bool {
            return $pendingBatch->jobs->sole() instanceof $firstJobClass;
        }),
        FinishRun::class,
    ]);
})->with([
    'issues' => [CollectionType::ISSUES, CollectIssues::class],
    'pull requests' => [CollectionType::PULL_REQUESTS, CollectPullRequests::class],
    'repositories' => [CollectionType::REPOSITORIES, CollectRepositories::class],
    'sites' => [CollectionType::SITES, CollectSites::class],
]);

test('start finishes the run with the succeeded event of the collection', function (
    CollectionType $collectionType,
    string $runSucceededClass,
): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(QueueingDispatcher::class, $busFake);

    $source = new Source(fake()->slug(), new GitHubDriver(fake()->slug(), fake()->sha1()));

    $collectionStarter = $this->app->make(CollectionStarter::class);
    $collectionStarter->start($source, $collectionType);

    $busFake->assertChained([
        ChainedBatch::class,
        static function (FinishRun $finishRun) use ($runSucceededClass): bool {
            return $finishRun->runSucceeded instanceof $runSucceededClass
                && $finishRun->runSucceeded->runId->equals($finishRun->runId);
        },
    ]);
})->with([
    'issues' => [CollectionType::ISSUES, IssuesRunSucceeded::class],
    'pull requests' => [CollectionType::PULL_REQUESTS, PullRequestsRunSucceeded::class],
    'repositories' => [CollectionType::REPOSITORIES, GitHubRunSucceeded::class],
    'sites' => [CollectionType::SITES, ForgeRunSucceeded::class],
]);

test('start records the run of the collection of the source', function (): void {
    $this->app->instance(QueueingDispatcher::class, new BusFake($this->app->make(QueueingDispatcher::class)));
    $source = new Source(fake()->slug(), new GitHubDriver(fake()->slug(), fake()->sha1()));
    $collectionType = fake()->randomElement(CollectionType::cases());

    $collectionStarter = $this->app->make(CollectionStarter::class);
    $collectionStarter->start($source, $collectionType);

    $this->assertDatabaseHas(Run::class, [
        'source' => $source->key,
        'collection' => $collectionType,
    ]);
});
