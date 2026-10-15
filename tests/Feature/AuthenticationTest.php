<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Tests\Feature;

use Maarheeze\Compass\Models\User;
use Maarheeze\Compass\Tests\TestCase;

use function route;
use function urlencode;

class AuthenticationTest extends TestCase
{
    public function testGuestsAreRedirectedToTheLoginPage(): void
    {
        $this->get(route('compass.dashboard'))->assertRedirect(route('compass.login'));
    }

    public function testTheLoginPageIsShownToGuests(): void
    {
        $this->get(route('compass.login'))
            ->assertOk()
            ->assertSee('Continue with GitHub');
    }

    public function testGuestsAreSentToGithubWithTheCallbackUrl(): void
    {
        $location = $this->get(route('compass.auth.github'))
            ->assertRedirect()
            ->headers
            ->get('Location');

        $this->assertStringStartsWith('https://github.com/login/oauth/authorize', $location);
        $this->assertStringContainsString('redirect_uri=' . urlencode(route('compass.auth.github.callback')), $location);
    }

    public function testSignedInUsersSeeTheDashboard(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'compass')
            ->get(route('compass.dashboard'))
            ->assertOk()
            ->assertSee($user->nickname);
    }

    public function testSignedInUsersAreRedirectedAwayFromTheLoginPage(): void
    {
        $this->actingAs(User::factory()->create(), 'compass')
            ->get(route('compass.login'))
            ->assertRedirect(route('compass.dashboard'));
    }

    public function testUsersCanSignOut(): void
    {
        $this->actingAs(User::factory()->create(), 'compass')
            ->post(route('compass.logout'))
            ->assertRedirect(route('compass.login'));

        $this->assertGuest('compass');
    }
}
