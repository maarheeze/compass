<?php

declare(strict_types=1);

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Log\Context\Repository as Context;
use Illuminate\Support\Testing\Fakes\EventFake;
use Maarheeze\Compass\Collector\Client\ClientStatus;
use Maarheeze\Compass\Collector\Client\Jobs\InspectSite;
use Maarheeze\Compass\Collector\Events\SiteInspected;
use Maarheeze\Compass\Models\Site;

test('handle stores the report of a site with the client installed', function (): void {
    $site = Site::factory()->create();
    $this->app->make(HttpFactory::class)->fake(['*' => HttpFactory::response([
        'app_debug' => fake()->boolean(),
        'app_env' => fake()->word(),
        'app_name' => fake()->company(),
        'endpoints' => [],
    ]),
    ]);

    $inspectSite = new InspectSite($site);
    $this->app->call([$inspectSite, 'handle']);

    $this->assertDatabaseHas(Site::class, [
        'id' => $site->id,
        'client_status' => ClientStatus::INSTALLED,
    ]);
});

test('handle clears the report of a site without a client', function (): void {
    $site = Site::factory()->create();
    $this->app->make(HttpFactory::class)->fake(['*' => HttpFactory::response(null, 404)]);

    $inspectSite = new InspectSite($site);
    $this->app->call([$inspectSite, 'handle']);

    $this->assertDatabaseHas(Site::class, [
        'id' => $site->id,
        'client_status' => ClientStatus::NOT_INSTALLED,
    ]);
});

test('handle marks a client that reports in another shape incompatible', function (): void {
    $site = Site::factory()->create();
    $this->app->make(HttpFactory::class)->fake(['*' => HttpFactory::response([
        'app_debug' => fake()->boolean(),
        'app_env' => fake()->word(),
        'app_name' => fake()->company(),
    ]),
    ]);

    $inspectSite = new InspectSite($site);
    $this->app->call([$inspectSite, 'handle']);

    $this->assertDatabaseHas(Site::class, [
        'id' => $site->id,
        'client_status' => ClientStatus::INCOMPATIBLE,
    ]);
});

test('handle marks a client that refuses the key unauthorized', function (): void {
    $site = Site::factory()->create();
    $this->app->make(HttpFactory::class)->fake(['*' => HttpFactory::response(null, 401)]);

    $inspectSite = new InspectSite($site);
    $this->app->call([$inspectSite, 'handle']);

    $this->assertDatabaseHas(Site::class, [
        'id' => $site->id,
        'client_status' => ClientStatus::UNAUTHORIZED,
    ]);
});

test('handle marks a site that cannot be reached unreachable', function (): void {
    $site = Site::factory()->create();
    $this->app->make(HttpFactory::class)->fake(['*' => HttpFactory::failedConnection()]);

    $inspectSite = new InspectSite($site);
    $this->app->call([$inspectSite, 'handle']);

    $this->assertDatabaseHas(Site::class, [
        'id' => $site->id,
        'client_status' => ClientStatus::UNREACHABLE,
    ]);
});

test('handle leaves a site without a key alone', function (): void {
    $site = Site::factory()->create([
        'client_key' => null,
        'client_status' => null,
    ]);
    $this->setConfiguration('compass.client.key', null);
    $http = $this->app->make(HttpFactory::class);
    $http->fake();

    $inspectSite = new InspectSite($site);
    $this->app->call([$inspectSite, 'handle']);

    $http->assertNothingSent();
});

test('handle adds the site to the log context', function (): void {
    $site = Site::factory()->create(['client_key' => null]);
    $this->setConfiguration('compass.client.key', null);

    $inspectSite = new InspectSite($site);
    $this->app->call([$inspectSite, 'handle']);

    expect($this->app->make(Context::class)->get('site'))
        ->toBe($site->name);
});

test('handle raises site inspected with the site', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));
    $this->app->instance(Dispatcher::class, $eventFake);
    $site = Site::factory()->create();
    $this->app->make(HttpFactory::class)->fake();

    $inspectSite = new InspectSite($site);
    $this->app->call([$inspectSite, 'handle']);

    $eventFake->assertDispatched(SiteInspected::class, static function (SiteInspected $siteInspected) use ($site): bool {
        return $siteInspected->site->is($site);
    });
});
