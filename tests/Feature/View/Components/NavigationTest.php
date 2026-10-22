<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Illuminate\Config\Repository as Config;
use Maarheeze\Compass\Models\Repository;
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

test('last collected at is when the last collection was collected', function (): void {
    $repositoryCollectedAt = CarbonImmutable::instance(fake()->dateTime());
    Repository::factory()->create(['collected_at' => $repositoryCollectedAt]);
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $navigation = $this->app->make(Navigation::class, ['section' => fake()->randomElement(NavigationSection::cases())]);

    expect($navigation->lastCollectedAt?->getTimestamp())
        ->toBe($repositoryCollectedAt->getTimestamp());
});

test('application name is the configured application name', function (): void {
    $applicationName = fake()->company();
    $this->app->make(Config::class)->set('app.name', $applicationName);
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $navigation = $this->app->make(Navigation::class, ['section' => fake()->randomElement(NavigationSection::cases())]);

    expect($navigation->applicationName)
        ->toBe($applicationName);
});
