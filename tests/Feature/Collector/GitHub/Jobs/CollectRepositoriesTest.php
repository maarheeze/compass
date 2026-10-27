<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Illuminate\Http\Client\Factory as HttpFactory;
use Maarheeze\Compass\Collector\GitHub\Jobs\CollectRepositories;
use Maarheeze\Compass\Collector\GitHub\RepositoryVisibility;
use Maarheeze\Compass\Collector\RunId;
use Maarheeze\Compass\Models\Repository;

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
    ];
    $runId = RunId::generate();
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

    $collectRepositories = new CollectRepositories($runId);
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

    $collectRepositories = new CollectRepositories(RunId::generate());
    $this->app->call([$collectRepositories, 'handle']);

    expect($repository->refresh()->missing_at)
        ->toBeInstanceOf(CarbonImmutable::class);
});
