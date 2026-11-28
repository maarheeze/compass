<?php

declare(strict_types=1);

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\Testing\Fakes\EventFake;
use Maarheeze\Compass\Collector\Events\SiteProcessesCollected;
use Maarheeze\Compass\Collector\SiteProcesses\Jobs\CollectSiteProcesses;
use Maarheeze\Compass\Collector\SiteProcesses\QueueWorkerState;
use Maarheeze\Compass\Models\Site;

test('handle stores the state of the queue worker forge lists for the site', function (): void {
    $source = fake()->slug();
    $this->setConfiguration('compass.sources', [
        $source => [
            'driver' => 'forge',
            'organisation' => fake()->slug(),
            'token' => fake()->sha1(),
            'requests_per_minute' => fake()->numberBetween(10, 100),
            'server_pattern' => '/.*/',
        ],
    ]);
    $site = Site::factory()->create([
        'source' => $source,
        'queue_worker_state' => null,
    ]);
    $this->app->make(HttpFactory::class)->fake([
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
            ],
            'meta' => [
                'next_cursor' => null,
            ],
        ]),
    ]);

    $collectSiteProcesses = new CollectSiteProcesses($site);
    $this->app->call([$collectSiteProcesses, 'handle']);

    $this->assertDatabaseHas(Site::class, [
        'id' => $site->id,
        'queue_worker_state' => QueueWorkerState::RUNNING,
    ]);
});

test('handle raises that the processes of the site were collected', function (): void {
    $source = fake()->slug();
    $this->setConfiguration('compass.sources', [
        $source => [
            'driver' => 'forge',
            'organisation' => fake()->slug(),
            'token' => fake()->sha1(),
            'requests_per_minute' => fake()->numberBetween(10, 100),
            'server_pattern' => '/.*/',
        ],
    ]);
    $eventFake = new EventFake($this->app->make(Dispatcher::class));
    $this->app->instance(Dispatcher::class, $eventFake);
    $site = Site::factory()->create(['source' => $source]);
    $this->app->make(HttpFactory::class)->fake([
        '*' => HttpFactory::response([
            'data' => [],
            'meta' => [
                'next_cursor' => null,
            ],
        ]),
    ]);

    $collectSiteProcesses = new CollectSiteProcesses($site);
    $this->app->call([$collectSiteProcesses, 'handle']);

    $eventFake->assertDispatched(SiteProcessesCollected::class, static function (SiteProcessesCollected $siteProcessesCollected) use ($site): bool {
        return $siteProcessesCollected->site === $site;
    });
});

test('lock key is the site', function (): void {
    $site = Site::factory()->create();

    $collectSiteProcesses = new CollectSiteProcesses($site);
    $lockKey = $collectSiteProcesses->lockKey();

    expect($lockKey)
        ->toBe($site->id->toString());
});

test('skips subject is false', function (): void {
    $collectSiteProcesses = new CollectSiteProcesses(Site::factory()->create());

    $skipsSubject = $collectSiteProcesses->skipsSubject();

    expect($skipsSubject)
        ->toBeFalse();
});

test('subject is the site', function (): void {
    $site = Site::factory()->create();

    $collectSiteProcesses = new CollectSiteProcesses($site);
    $subject = $collectSiteProcesses->subject();

    expect($subject)
        ->toBe($site);
});
