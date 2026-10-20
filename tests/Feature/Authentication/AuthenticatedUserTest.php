<?php

declare(strict_types=1);

use Maarheeze\Compass\Authentication\AuthenticatedUser;
use Maarheeze\Compass\Models\User;
use Maarheeze\Compass\Providers\AuthenticationProvider;

test('user is the user signed in on the compass guard', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user, AuthenticationProvider::GUARD);

    $authenticatedUser = $this->app->make(AuthenticatedUser::class);

    expect($authenticatedUser->user())
        ->toBe($user);
});
