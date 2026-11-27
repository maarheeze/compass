<?php

declare(strict_types=1);

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Log\Context\Repository as Context;
use Illuminate\Support\Testing\Fakes\EventFake;
use Maarheeze\Compass\Collector\CollectingFailed;
use Maarheeze\Compass\Collector\Events\SiteInspected;
use Maarheeze\Compass\Collector\SiteReports\Jobs\InspectSite;
use Maarheeze\Compass\Collector\SiteReports\SiteReportStatus;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\Models\SiteReport;

test('handle stores the report the collector of its source collected', function (): void {
    $source = fake()->slug();
    $this->setConfiguration('compass.sources', [
        $source => [
            'driver' => 'compass-client',
            'path' => sprintf('/%s', fake()->slug()),
            'scheduler_grace_minutes' => fake()->numberBetween(1, 60),
        ],
    ]);
    $site = Site::factory()->create();
    $this->app->make(HttpFactory::class)->fake(['*' => HttpFactory::response([
        'app_debug' => fake()->boolean(),
        'app_env' => fake()->word(),
        'app_name' => fake()->company(),
        'maintenance_mode' => fake()->boolean(),
        'endpoints' => [],
        'scheduler' => [
            'last_ran_at' => null,
            'tasks' => [],
        ],
        'queue_driver' => fake()->slug(),
    ]),
    ]);

    $inspectSite = new InspectSite($site, $source);
    $this->app->call([$inspectSite, 'handle']);

    $this->assertDatabaseHas(SiteReport::class, [
        'site_id' => $site->id,
        'source' => $source,
        'status' => SiteReportStatus::INSTALLED,
    ]);
});

test('handle stores the scheduler grace minutes of its source on the report', function (): void {
    $source = fake()->slug();
    $schedulerGraceMinutes = fake()->numberBetween(1, 60);
    $this->setConfiguration('compass.sources', [
        $source => [
            'driver' => 'compass-client',
            'path' => sprintf('/%s', fake()->slug()),
            'scheduler_grace_minutes' => $schedulerGraceMinutes,
        ],
    ]);
    $site = Site::factory()->create();
    $this->app->make(HttpFactory::class)->fake(['*' => HttpFactory::response(null, 404)]);

    $inspectSite = new InspectSite($site, $source);
    $this->app->call([$inspectSite, 'handle']);

    $this->assertDatabaseHas(SiteReport::class, [
        'site_id' => $site->id,
        'source' => $source,
        'scheduler_grace_minutes' => $schedulerGraceMinutes,
    ]);
});

test('handle asks the site with its key', function (): void {
    $source = fake()->slug();
    $this->setConfiguration('compass.sources', [
        $source => [
            'driver' => 'compass-client',
            'path' => sprintf('/%s', fake()->slug()),
            'scheduler_grace_minutes' => fake()->numberBetween(1, 60),
        ],
    ]);
    $site = Site::factory()->create(['key' => fake()->sha1()]);
    $http = $this->app->make(HttpFactory::class);
    $http->fake(['*' => HttpFactory::response(null, 404)]);

    $inspectSite = new InspectSite($site, $source);
    $this->app->call([$inspectSite, 'handle']);

    $http->assertSent(static function (HttpRequest $request) use ($site): bool {
        return $request->hasHeader('Authorization', sprintf('Bearer %s', $site->key));
    });
});

test('handle fails when the site cannot be reached', function (): void {
    $source = fake()->slug();
    $this->setConfiguration('compass.sources', [
        $source => [
            'driver' => 'compass-client',
            'path' => sprintf('/%s', fake()->slug()),
            'scheduler_grace_minutes' => fake()->numberBetween(1, 60),
        ],
    ]);
    $this->app->make(HttpFactory::class)->fake(['*' => HttpFactory::failedConnection()]);

    $inspectSite = new InspectSite(Site::factory()->create(), $source);
    $this->app->call([$inspectSite, 'handle']);
})->throws(CollectingFailed::class);

test('handle leaves a missing site alone', function (): void {
    $source = fake()->slug();
    $this->setConfiguration('compass.sources', [
        $source => [
            'driver' => 'compass-client',
            'path' => sprintf('/%s', fake()->slug()),
            'scheduler_grace_minutes' => fake()->numberBetween(1, 60),
        ],
    ]);
    $site = Site::factory()->create(['missing_at' => fake()->dateTime()]);
    $http = $this->app->make(HttpFactory::class);
    $http->fake();

    $inspectSite = new InspectSite($site, $source);
    $this->app->call([$inspectSite, 'handle']);

    $http->assertNothingSent();
});

test('handle adds the site to the log context', function (): void {
    $source = fake()->slug();
    $this->setConfiguration('compass.sources', [
        $source => [
            'driver' => 'compass-client',
            'path' => sprintf('/%s', fake()->slug()),
            'scheduler_grace_minutes' => fake()->numberBetween(1, 60),
        ],
    ]);
    $site = Site::factory()->create();
    $this->app->make(HttpFactory::class)->fake(['*' => HttpFactory::response(null, 404)]);

    $inspectSite = new InspectSite($site, $source);
    $this->app->call([$inspectSite, 'handle']);

    expect($this->app->make(Context::class)->get('site'))
        ->toBe($site->name);
});

test('handle raises site inspected with the report of the site for its source', function (): void {
    $source = fake()->slug();
    $this->setConfiguration('compass.sources', [
        $source => [
            'driver' => 'compass-client',
            'path' => sprintf('/%s', fake()->slug()),
            'scheduler_grace_minutes' => fake()->numberBetween(1, 60),
        ],
    ]);
    $eventFake = new EventFake($this->app->make(Dispatcher::class));
    $this->app->instance(Dispatcher::class, $eventFake);
    $site = Site::factory()->create();
    $this->app->make(HttpFactory::class)->fake(['*' => HttpFactory::response(null, 404)]);

    $inspectSite = new InspectSite($site, $source);
    $this->app->call([$inspectSite, 'handle']);

    $eventFake->assertDispatched(SiteInspected::class, static function (SiteInspected $siteInspected) use ($site, $source): bool {
        return $siteInspected->siteReport->site_id->equals($site->id)
            && $siteInspected->siteReport->source === $source;
    });
});

test('handle refuses a source without a site reports collector', function (): void {
    $source = fake()->slug();
    $this->setConfiguration('compass.sources', [
        $source => [
            'driver' => fake()->slug(),
        ],
    ]);

    $inspectSite = new InspectSite(Site::factory()->create(), $source);
    $this->app->call([$inspectSite, 'handle']);
})->throws(InvalidArgumentException::class);

test('lock key is the site and the source', function (): void {
    $site = Site::factory()->create();
    $source = fake()->slug();

    $inspectSite = new InspectSite($site, $source);
    $lockKey = $inspectSite->lockKey();

    expect($lockKey)
        ->toBe(sprintf('%s:%s', $site->id->toString(), $source));
});

test('skips subject for a missing site', function (): void {
    $site = Site::factory()->create(['missing_at' => fake()->dateTime()]);

    $inspectSite = new InspectSite($site, fake()->slug());
    $skipsSubject = $inspectSite->skipsSubject();

    expect($skipsSubject)
        ->toBeTrue();
});

test('skips subject is false for a collected site', function (): void {
    $site = Site::factory()->create();

    $inspectSite = new InspectSite($site, fake()->slug());
    $skipsSubject = $inspectSite->skipsSubject();

    expect($skipsSubject)
        ->toBeFalse();
});

test('subject is the site', function (): void {
    $site = Site::factory()->create();

    $inspectSite = new InspectSite($site, fake()->slug());
    $subject = $inspectSite->subject();

    expect($subject)
        ->toBe($site);
});
