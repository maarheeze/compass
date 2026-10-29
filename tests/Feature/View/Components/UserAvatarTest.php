<?php

declare(strict_types=1);

use Maarheeze\Compass\Models\User;
use Maarheeze\Compass\View\Components\UserAvatar;

test('render renders the user avatar view', function (): void {
    $userAvatar = $this->app->make(UserAvatar::class, ['user' => User::factory()->create()]);

    expect($userAvatar->render()->name())
        ->toBe('compass::components.user-avatar');
});
