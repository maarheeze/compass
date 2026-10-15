<?php

declare(strict_types=1);

use Maarheeze\Compass\Models\User;

test('guests are redirected to the login page', function (): void {
    $this->get(route('compass.dashboard'))->assertRedirect(route('compass.login'));
});

test('the login page is shown to guests', function (): void {
    $this->get(route('compass.login'))
        ->assertOk()
        ->assertSee('Continue with GitHub');
});

test('guests are sent to github with the callback url', function (): void {
    $location = $this->get(route('compass.auth.github'))
        ->assertRedirect()
        ->headers
        ->get('Location');

    expect($location)
        ->toStartWith('https://github.com/login/oauth/authorize')
        ->toContain('redirect_uri=' . urlencode(route('compass.auth.github.callback')));
});

test('signed in users see the dashboard', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user, 'compass')
        ->get(route('compass.dashboard'))
        ->assertOk()
        ->assertSee($user->nickname);
});

test('signed in users are redirected away from the login page', function (): void {
    $this->actingAs(User::factory()->create(), 'compass')
        ->get(route('compass.login'))
        ->assertRedirect(route('compass.dashboard'));
});

test('users can sign out', function (): void {
    $this->actingAs(User::factory()->create(), 'compass')
        ->post(route('compass.logout'))
        ->assertRedirect(route('compass.login'));

    $this->assertGuest('compass');
});
