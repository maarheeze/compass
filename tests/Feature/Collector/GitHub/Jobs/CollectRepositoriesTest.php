<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Illuminate\Http\Client\Factory as HttpFactory;
use Maarheeze\Compass\Collector\GitHub\Jobs\CollectChangedComposerJsons;
use Maarheeze\Compass\Collector\GitHub\Jobs\CollectChangedComposerLocks;
use Maarheeze\Compass\Collector\GitHub\Jobs\CollectChangedDependabotYmls;
use Maarheeze\Compass\Collector\GitHub\Jobs\CollectIssuesAndPullRequests;
use Maarheeze\Compass\Collector\GitHub\Jobs\CollectRepositories;
use Maarheeze\Compass\Collector\GitHub\RepositoryVisibility;
use Maarheeze\Compass\Models\Repository;
use Maarheeze\Uuid\Uuid;

test('handle stores the repositories of the page', function (): void {
    $repositoryResponse = [
        'id' => fake()->sha1(),
        'name' => fake()->slug(),
        'url' => fake()->url(),
        'visibility' => fake()->randomElement(RepositoryVisibility::cases())->value,
        'defaultBranchRef' => [
            'name' => fake()->word(),
            'target' => [
                'oid' => fake()->sha1(),
                'committedDate' => fake()->iso8601(),
            ],
        ],
        'composerJson' => null,
        'composerLock' => null,
        'packageJson' => null,
        'dependabotYml' => null,
    ];
    $runId = Uuid::generate();
    $this->app->make(HttpFactory::class)->fake([
        '*' => HttpFactory::response([
            'data' => [
                'organization' => [
                    'repositories' => [
                        'pageInfo' => [
                            'hasNextPage' => false,
                            'endCursor' => null,
                        ],
                        'nodes' => [$repositoryResponse],
                    ],
                ],
            ],
        ]),
    ]);

    [$collectRepositories] = new CollectRepositories($runId)->withFakeBatch();
    $this->app->call([$collectRepositories, 'handle']);

    $this->assertDatabaseHas(Repository::class, [
        'name' => $repositoryResponse['name'],
        'run_id' => $runId,
    ]);
});

test('handle marks the repositories of another run missing on the last page', function (): void {
    $repository = Repository::factory()->create();
    $this->app->make(HttpFactory::class)->fake([
        '*' => HttpFactory::response([
            'data' => [
                'organization' => [
                    'repositories' => [
                        'pageInfo' => [
                            'hasNextPage' => false,
                            'endCursor' => null,
                        ],
                        'nodes' => [],
                    ],
                ],
            ],
        ]),
    ]);

    [$collectRepositories] = new CollectRepositories(Uuid::generate())->withFakeBatch();
    $this->app->call([$collectRepositories, 'handle']);

    expect($repository->refresh()->missing_at)
        ->toBeInstanceOf(CarbonImmutable::class);
});

test('handle adds the collection of the issues, pull requests, composer files and dependabot yml to its batch', function (): void {
    $this->app->make(HttpFactory::class)->fake([
        '*' => HttpFactory::response([
            'data' => [
                'organization' => [
                    'repositories' => [
                        'pageInfo' => [
                            'hasNextPage' => false,
                            'endCursor' => null,
                        ],
                        'nodes' => [],
                    ],
                ],
            ],
        ]),
    ]);

    [$collectRepositories, $batchFake] = new CollectRepositories(Uuid::generate())->withFakeBatch();
    $this->app->call([$collectRepositories, 'handle']);

    expect(array_map(get_class(...), $batchFake->added))
        ->toBe([
            CollectIssuesAndPullRequests::class,
            CollectChangedComposerJsons::class,
            CollectChangedComposerLocks::class,
            CollectChangedDependabotYmls::class,
        ]);
});
