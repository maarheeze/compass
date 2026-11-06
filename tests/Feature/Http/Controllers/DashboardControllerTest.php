<?php

declare(strict_types=1);

use Maarheeze\Compass\Collections\AlertCollection;
use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\Alert;
use Maarheeze\Compass\Models\User;
use Maarheeze\Compass\Providers\AuthenticationProvider;

test('show contains the alerts that are not done, newest first', function (): void {
    $earlierAlert = Alert::factory()
        ->resolved()
        ->create(['created_at' => fake()->dateTimeBetween('-2 months', '-1 month')]);
    $laterAlert = Alert::factory()
        ->active()
        ->create(['created_at' => fake()->dateTimeBetween('-1 month')]);
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::DASHBOARD));

    $response->assertViewHas('alerts', static function (AlertCollection $alerts) use ($earlierAlert, $laterAlert): bool {
        return $alerts->count() === 2
            && $alerts->first()->id->equals($laterAlert->id)
            && $alerts->last()->id->equals($earlierAlert->id);
    });
});

test('show leaves done alerts out of the alerts', function (): void {
    Alert::factory()
        ->done()
        ->create();
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::DASHBOARD));

    $response->assertViewHas('alerts', static function (AlertCollection $alerts): bool {
        return $alerts->isEmpty();
    });
});

test('show loads the assignee of the alerts', function (): void {
    Alert::factory()
        ->active()
        ->create();
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::DASHBOARD));

    $response->assertViewHas('alerts', static function (AlertCollection $alerts): bool {
        return $alerts->sole()->relationLoaded('assignee');
    });
});
