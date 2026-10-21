<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Http\Controllers;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Maarheeze\Compass\Analyzer\Composer\ComposerWarningFinder;
use Maarheeze\Compass\Authentication\AuthenticatedUser;

readonly class DashboardController
{
    public function __construct(
        private AuthenticatedUser $authenticatedUser,
        private ComposerWarningFinder $composerWarningFinder,
        private ViewFactory $view,
    ) {
    }

    public function show(): View
    {
        return $this->view->make('compass::dashboard', [
            'repositoriesWithComposerWarning' => $this->composerWarningFinder->findRepositories(),
            'user' => $this->authenticatedUser->user(),
        ]);
    }
}
