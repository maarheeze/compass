<?php

declare(strict_types=1);

use Maarheeze\Compass\Models\User;
use Maarheeze\Compass\Providers\AuthenticationProvider;
use Maarheeze\Compass\View\Components\Navigation;
use Maarheeze\Compass\View\NavigationSection;

test('user is the signed in user', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user, AuthenticationProvider::GUARD);

    $navigation = $this->app->make(Navigation::class, ['section' => fake()->randomElement(NavigationSection::cases())]);

    expect($navigation->user->is($user))
        ->toBeTrue();
});
