<?php

declare(strict_types=1);

use Maarheeze\Compass\Broadcasting\CompassChannel;
use Maarheeze\Compass\Models\User;

test('join admits every signed in user', function (): void {
    $user = User::factory()->create();

    $compassChannel = new CompassChannel();
    $isJoined = $compassChannel->join($user);

    expect($isJoined)
        ->toBeTrue();
});
