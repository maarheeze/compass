<?php

declare(strict_types=1);

use Illuminate\Contracts\Console\Kernel;
use Maarheeze\Compass\Console\GenerateKeyCommand;

test('handle prints a new key', function (): void {
    $kernel = $this->app->make(Kernel::class);

    $kernel->call(GenerateKeyCommand::SIGNATURE);

    expect($kernel->output())
        ->toMatch('/\A[0-9a-f]{64}\n\z/');
});
