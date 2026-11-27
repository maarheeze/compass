<?php

declare(strict_types=1);

use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\Request as HttpRequest;
use Maarheeze\Compass\Collector\Drivers\Forge\ForgeClient;
use Maarheeze\Compass\Collector\Drivers\Forge\SiteProcessesQuery;
use Maarheeze\Compass\Collector\RequestBudget;
use Maarheeze\Compass\Collector\SiteProcesses\CollectedSiteProcesses;
use Maarheeze\Compass\Collector\SiteProcesses\QueueWorkerState;
use Maarheeze\Compass\Models\Site;

test('site processes request the background processes of the site on its server', function (): void {
    $organisation = fake()->slug();
    $site = Site::factory()->create();
    $http = $this->app->make(HttpFactory::class);
    $http->fake([
        '*' => HttpFactory::response([
            'data' => [],
            'meta' => [
                'next_cursor' => null,
            ],
        ]),
    ]);

    $siteProcessesQuery = new SiteProcessesQuery(
        new ForgeClient(
            $http,
            fake()->sha1(),
            new RequestBudget($this->app->make(RateLimiter::class), fake()->slug(), fake()->numberBetween(10, 100)),
        ),
        $organisation,
    );
    $siteProcessesQuery->siteProcesses($site);

    $http->assertSent(static function (HttpRequest $request) use ($organisation, $site): bool {
        return str_starts_with($request->url(), sprintf(
            'https://forge.laravel.com/api/orgs/%s/servers/%s/background-processes?',
            $organisation,
            $site->server_external_id,
        ))
            && $request->data()['filter[site_id]'] === $site->external_id;
    });
});

test('site processes have the state of the queue worker forge lists', function (
    string $status,
    QueueWorkerState $queueWorkerState,
): void {
    $http = $this->app->make(HttpFactory::class);
    $http->fake([
        '*' => HttpFactory::response([
            'data' => [
                [
                    'id' => fake()->numerify('####'),
                    'type' => 'backgroundProcesses',
                    'attributes' => [
                        'command' => sprintf('php artisan queue:work %s', fake()->word()),
                        'status' => $status,
                    ],
                ],
            ],
            'meta' => [
                'next_cursor' => null,
            ],
        ]),
    ]);

    $siteProcessesQuery = new SiteProcessesQuery(
        new ForgeClient(
            $http,
            fake()->sha1(),
            new RequestBudget($this->app->make(RateLimiter::class), fake()->slug(), fake()->numberBetween(10, 100)),
        ),
        fake()->slug(),
    );
    $collectedSiteProcesses = $siteProcessesQuery->siteProcesses(Site::factory()->create());

    expect($collectedSiteProcesses)
        ->toEqual(new CollectedSiteProcesses($queueWorkerState));
})->with([
    'running' => ['running', QueueWorkerState::RUNNING],
    'installing' => ['installing', QueueWorkerState::STARTING],
    'starting' => ['starting', QueueWorkerState::STARTING],
    'restarting' => ['restarting', QueueWorkerState::STARTING],
    'stopping' => ['stopping', QueueWorkerState::STOPPED],
    'stopped' => ['stopped', QueueWorkerState::STOPPED],
    'removing' => ['removing', QueueWorkerState::STOPPED],
    'fatal' => ['fatal', QueueWorkerState::FAILING],
    'backoff' => ['backoff', QueueWorkerState::FAILING],
    'exited' => ['exited', QueueWorkerState::FAILING],
    'unknown' => ['unknown', QueueWorkerState::UNKNOWN],
]);

test('site processes have the worst state of several queue workers', function (): void {
    $http = $this->app->make(HttpFactory::class);
    $http->fake([
        '*' => HttpFactory::response([
            'data' => [
                [
                    'id' => fake()->numerify('####'),
                    'type' => 'backgroundProcesses',
                    'attributes' => [
                        'command' => 'php artisan queue:work',
                        'status' => 'running',
                    ],
                ],
                [
                    'id' => fake()->numerify('####'),
                    'type' => 'backgroundProcesses',
                    'attributes' => [
                        'command' => 'php artisan queue:work',
                        'status' => 'fatal',
                    ],
                ],
            ],
            'meta' => [
                'next_cursor' => null,
            ],
        ]),
    ]);

    $siteProcessesQuery = new SiteProcessesQuery(
        new ForgeClient(
            $http,
            fake()->sha1(),
            new RequestBudget($this->app->make(RateLimiter::class), fake()->slug(), fake()->numberBetween(10, 100)),
        ),
        fake()->slug(),
    );
    $collectedSiteProcesses = $siteProcessesQuery->siteProcesses(Site::factory()->create());

    expect($collectedSiteProcesses)
        ->toEqual(new CollectedSiteProcesses(QueueWorkerState::FAILING));
});

test('site processes have no queue worker configured when no background process runs queue work', function (): void {
    $http = $this->app->make(HttpFactory::class);
    $http->fake([
        '*' => HttpFactory::response([
            'data' => [
                [
                    'id' => fake()->numerify('####'),
                    'type' => 'backgroundProcesses',
                    'attributes' => [
                        'command' => 'php artisan reverb:start',
                        'status' => 'running',
                    ],
                ],
            ],
            'meta' => [
                'next_cursor' => null,
            ],
        ]),
    ]);

    $siteProcessesQuery = new SiteProcessesQuery(
        new ForgeClient(
            $http,
            fake()->sha1(),
            new RequestBudget($this->app->make(RateLimiter::class), fake()->slug(), fake()->numberBetween(10, 100)),
        ),
        fake()->slug(),
    );
    $collectedSiteProcesses = $siteProcessesQuery->siteProcesses(Site::factory()->create());

    expect($collectedSiteProcesses)
        ->toEqual(new CollectedSiteProcesses(QueueWorkerState::NOT_CONFIGURED));
});

test('site processes request the next page from the cursor of the page before it', function (): void {
    $nextCursor = fake()->sha1();
    $http = $this->app->make(HttpFactory::class);
    $responseSequence = $http->sequence();
    $responseSequence->push([
        'data' => [],
        'meta' => [
            'next_cursor' => $nextCursor,
        ],
    ]);
    $responseSequence->push([
        'data' => [],
        'meta' => [
            'next_cursor' => null,
        ],
    ]);
    $http->fake(['*' => $responseSequence]);

    $siteProcessesQuery = new SiteProcessesQuery(
        new ForgeClient(
            $http,
            fake()->sha1(),
            new RequestBudget($this->app->make(RateLimiter::class), fake()->slug(), fake()->numberBetween(10, 100)),
        ),
        fake()->slug(),
    );
    $siteProcessesQuery->siteProcesses(Site::factory()->create());

    $http->assertSent(static function (HttpRequest $request) use ($nextCursor): bool {
        return $request->data()['page[cursor]'] === $nextCursor;
    });
});
