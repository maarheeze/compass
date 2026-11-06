<?php

declare(strict_types=1);

use Maarheeze\Compass\Models\User;
use Maarheeze\Compass\View\Components\Avatar;

test('render renders the avatar view', function (): void {
    $avatar = $this->app->make(Avatar::class, [
        'person' => User::factory()->create(),
        'url' => fake()->optional()->url(),
    ]);

    expect($avatar->render()->name())
        ->toBe('compass::components.avatar');
});
