<?php

declare(strict_types=1);

use Maarheeze\Compass\Models\User;

test('to array leaves out the github token', function (): void {
    $user = User::factory()->create();

    expect($user->toArray())
        ->not->toHaveKey('github_token');
});
