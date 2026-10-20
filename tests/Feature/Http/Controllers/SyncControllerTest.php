<?php

declare(strict_types=1);

use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Support\Testing\Fakes\BusFake;
use Maarheeze\Compass\Collector\Jobs\CollectRepositories;
use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\User;
use Maarheeze\Compass\Providers\AuthenticationProvider;

test('github starts a collection of the repositories', function (): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(Dispatcher::class, $busFake);
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $this->post(route(RouteName::SYNC_GITHUB));

    expect($busFake->dispatched(CollectRepositories::class))
        ->toHaveCount(1);
});

test('github sends the user back to the dashboard', function (): void {
    $this->app->instance(Dispatcher::class, new BusFake($this->app->make(QueueingDispatcher::class)));
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->post(route(RouteName::SYNC_GITHUB));

    $response->assertRedirect(route(RouteName::DASHBOARD));
});
