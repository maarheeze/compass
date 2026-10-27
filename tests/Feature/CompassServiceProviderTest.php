<?php

declare(strict_types=1);

use Livewire\Mechanisms\PersistentMiddleware\PersistentMiddleware;
use Maarheeze\Compass\Http\Middleware\Authenticate;
use Maarheeze\Compass\Livewire\SyncButton;

test('livewire updates keep requiring a signed in user', function (): void {
    $persistentMiddleware = $this->app->make(PersistentMiddleware::class)->getPersistentMiddleware();

    expect($persistentMiddleware)
        ->toContain(Authenticate::class);
});

test('livewire components resolve under the compass namespace', function (): void {
    $finder = $this->app->make('livewire.finder');

    expect($finder->resolveClassComponentClassName('compass::sync-button'))
        ->toBe(SyncButton::class);
});
