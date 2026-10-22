<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Http\Controllers;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Maarheeze\Compass\Console\SyncForgeCommand;
use Maarheeze\Compass\Console\SyncGitHubCommand;
use Maarheeze\Compass\Http\RouteName;

readonly class SyncController
{
    public function __construct(
        private Kernel $kernel,
        private Redirector $redirector,
    ) {
    }

    public function __invoke(): RedirectResponse
    {
        $this->kernel->call(SyncGitHubCommand::SIGNATURE);
        $this->kernel->call(SyncForgeCommand::SIGNATURE);

        return $this->redirector->route(RouteName::DASHBOARD);
    }
}
