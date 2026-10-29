<?php

declare(strict_types=1);

use Maarheeze\Compass\Models\User;
use Maarheeze\Compass\View\Components\UserLabel;

test('render renders the user label view', function (): void {
    $userLabel = $this->app->make(UserLabel::class, ['user' => User::factory()->create()]);

    expect($userLabel->render()->name())
        ->toBe('compass::components.user-label');
});
