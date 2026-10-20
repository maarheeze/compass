<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Http\Controllers;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Maarheeze\Compass\Authentication\AuthenticatedUser;
use Maarheeze\Compass\Models\Repository;

readonly class DashboardController
{
    public function __construct(
        private AuthenticatedUser $authenticatedUser,
        private ViewFactory $view,
    ) {
    }

    public function show(): View
    {
        return $this->view->make('compass::dashboard', [
            'repositories' => Repository::query()->orderBy('name')->get(),
            'user' => $this->authenticatedUser->user(),
        ]);
    }
}
