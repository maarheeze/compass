<?php

declare(strict_types=1);

use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Testing\Fakes\BusFake;
use Maarheeze\Compass\Collector\CollectionRunId;
use Maarheeze\Compass\Collector\Jobs\CollectRepositories;
use Maarheeze\Compass\Models\Repository;

test('handle requests the url', function (): void {
    $url = fake()->url();

    $http = $this->app->make(HttpFactory::class);
    $http->fake(['*' => HttpFactory::response([])]);

    $collectRepositories = new CollectRepositories($url, CollectionRunId::generate());
    $this->app->call([$collectRepositories, 'handle']);

    $http->assertSent(static function (HttpRequest $request) use ($url): bool {
        return $request->url() === $url;
    });
});

test('handle stores the repositories in the response', function (): void {
    $repositoryResponse = [
        'name' => fake()->slug(),
        'default_branch' => fake()->word(),
        'archived' => false,
    ];
    $collectionRunId = CollectionRunId::generate();
    $this->app->make(HttpFactory::class)->fake(['*' => HttpFactory::response([$repositoryResponse])]);

    $collectRepositories = new CollectRepositories(fake()->url(), $collectionRunId);
    $this->app->call([$collectRepositories, 'handle']);

    $this->assertDatabaseHas(Repository::class, [
        'name' => $repositoryResponse['name'],
        'default_branch' => $repositoryResponse['default_branch'],
        'collection_run_id' => $collectionRunId,
    ]);
});

test('handle skips archived repositories', function (): void {
    $repositoryResponse = [
        'name' => fake()->slug(),
        'default_branch' => fake()->word(),
        'archived' => true,
    ];
    $this->app->make(HttpFactory::class)->fake(['*' => HttpFactory::response([$repositoryResponse])]);

    $collectRepositories = new CollectRepositories(fake()->url(), CollectionRunId::generate());
    $this->app->call([$collectRepositories, 'handle']);

    $this->assertDatabaseMissing(Repository::class, ['name' => $repositoryResponse['name']]);
});

test('handle dispatches the next page when the response links to one', function (): void {
    $nextPageUrl = fake()->url();
    $collectionRunId = CollectionRunId::generate();
    $this->app->make(HttpFactory::class)->fake([
        '*' => HttpFactory::response([], 200, ['Link' => sprintf('<%s>; rel="next", <%s>; rel="last"', $nextPageUrl, fake()->url())]),
    ]);

    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(Dispatcher::class, $busFake);

    $collectRepositories = new CollectRepositories(fake()->url(), $collectionRunId);
    $this->app->call([$collectRepositories, 'handle']);

    $expectedCollectRepositories = new CollectRepositories($nextPageUrl, $collectionRunId);
    expect($busFake->dispatched(CollectRepositories::class)->all())
        ->toEqual([$expectedCollectRepositories]);
});

test('handle marks repositories of another run inactive when the response links to no next page', function (): void {
    $repository = Repository::factory()->create(['is_active' => true]);
    $this->app->make(HttpFactory::class)->fake(['*' => HttpFactory::response([])]);

    $collectRepositories = new CollectRepositories(fake()->url(), CollectionRunId::generate());
    $this->app->call([$collectRepositories, 'handle']);

    $this->assertDatabaseHas(Repository::class, [
        'id' => $repository->id,
        'is_active' => false,
    ]);
});

test('handle throws when github refuses the request', function (): void {
    $this->app->make(HttpFactory::class)->fake(['*' => HttpFactory::response(null, 401)]);

    $collectRepositories = new CollectRepositories(fake()->url(), CollectionRunId::generate());
    $this->app->call([$collectRepositories, 'handle']);
})->throws(RequestException::class);
