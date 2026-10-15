<?php

namespace Maarheeze\Compass\Tests\Feature;

use Maarheeze\Compass\Models\User;
use Maarheeze\Compass\Tests\TestCase;

class AuthenticationTest extends TestCase
{
    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $this->get(route('compass.dashboard'))->assertRedirect(route('compass.login'));
    }

    public function test_the_login_page_is_shown_to_guests(): void
    {
        $this->get(route('compass.login'))->assertOk()->assertSee('Continue with GitHub');
    }

    public function test_guests_are_sent_to_github_with_the_callback_url(): void
    {
        $location = $this->get(route('compass.auth.github'))->assertRedirect()->headers->get('Location');

        $this->assertStringStartsWith('https://github.com/login/oauth/authorize', $location);
        $this->assertStringContainsString('redirect_uri='.urlencode(route('compass.auth.github.callback')), $location);
    }

    public function test_signed_in_users_see_the_dashboard(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'compass')
            ->get(route('compass.dashboard'))
            ->assertOk()
            ->assertSee($user->nickname);
    }

    public function test_signed_in_users_are_redirected_away_from_the_login_page(): void
    {
        $this->actingAs(User::factory()->create(), 'compass')
            ->get(route('compass.login'))
            ->assertRedirect(route('compass.dashboard'));
    }

    public function test_users_can_sign_out(): void
    {
        $this->actingAs(User::factory()->create(), 'compass')
            ->post(route('compass.logout'))
            ->assertRedirect(route('compass.login'));

        $this->assertGuest('compass');
    }
}
