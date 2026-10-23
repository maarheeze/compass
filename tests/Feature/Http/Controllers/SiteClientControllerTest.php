<?php

declare(strict_types=1);

use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Support\Testing\Fakes\BusFake;
use Maarheeze\Compass\Collector\Client\Jobs\InspectSite;
use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\Models\User;
use Maarheeze\Compass\Providers\AuthenticationProvider;

test('edit contains the site', function (): void {
    $site = Site::factory()->create();
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::FORGE_SITE_CLIENT_EDIT, $site));

    $response->assertViewHas('site', static function (Site $viewSite) use ($site): bool {
        return $viewSite->is($site);
    });
});

test('update stores the path and key of the site', function (): void {
    $site = Site::factory()->create();
    $clientPath = sprintf('/%s', fake()->slug());
    $clientKey = fake()->sha1();
    $this->app->instance(Dispatcher::class, new BusFake($this->app->make(QueueingDispatcher::class)));
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $this->patch(route(RouteName::FORGE_SITE_CLIENT_UPDATE, $site), [
        'client_path' => $clientPath,
        'client_key' => $clientKey,
    ]);

    $this->assertDatabaseHas(Site::class, [
        'id' => $site->id,
        'client_path' => $clientPath,
        'client_key' => $clientKey,
    ]);
});

test('update clears the path and key of the site when left empty', function (): void {
    $site = Site::factory()->create([
        'client_path' => sprintf('/%s', fake()->slug()),
        'client_key' => fake()->sha1(),
    ]);
    $this->app->instance(Dispatcher::class, new BusFake($this->app->make(QueueingDispatcher::class)));
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $this->patch(route(RouteName::FORGE_SITE_CLIENT_UPDATE, $site), [
        'client_path' => '',
        'client_key' => '',
    ]);

    $this->assertDatabaseHas(Site::class, [
        'id' => $site->id,
        'client_path' => null,
        'client_key' => null,
    ]);
});

test('update queues an inspection of the site', function (): void {
    $site = Site::factory()->create();
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(Dispatcher::class, $busFake);
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $this->patch(route(RouteName::FORGE_SITE_CLIENT_UPDATE, $site), []);

    $busFake->assertDispatched(
        InspectSite::class,
        static function (InspectSite $inspectSite) use ($site): bool {
            return $inspectSite->site->is($site);
        },
    );
});

test('update refuses a path without a leading slash', function (): void {
    $site = Site::factory()->create();
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->patch(route(RouteName::FORGE_SITE_CLIENT_UPDATE, $site), ['client_path' => fake()->slug()]);

    $response->assertSessionHasErrors('client_path');
});

test('update sends the user back to the site', function (): void {
    $site = Site::factory()->create();
    $this->app->instance(Dispatcher::class, new BusFake($this->app->make(QueueingDispatcher::class)));
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->patch(route(RouteName::FORGE_SITE_CLIENT_UPDATE, $site), []);

    $response->assertRedirect(route(RouteName::FORGE_SITE, $site));
});

test('edit contains the path that was entered before a refusal', function (): void {
    $site = Site::factory()->create();
    $enteredPath = fake()->slug();
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->withSession(['_old_input' => ['client_path' => $enteredPath]])
        ->get(route(RouteName::FORGE_SITE_CLIENT_EDIT, $site));

    $response->assertViewHas('clientPath', $enteredPath);
});

test('edit shows why a path was refused', function (): void {
    $site = Site::factory()->create();
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->followingRedirects()
        ->from(route(RouteName::FORGE_SITE_CLIENT_EDIT, $site))
        ->patch(route(RouteName::FORGE_SITE_CLIENT_UPDATE, $site), ['client_path' => fake()->slug()]);

    $response->assertSeeText('The path must start with a /.');
});
