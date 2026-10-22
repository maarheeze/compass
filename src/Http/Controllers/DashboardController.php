<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Http\Controllers;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Maarheeze\Compass\Analyzer\AlertFinder;
use Maarheeze\Compass\Analyzer\GitHub\IssueCounter;
use Maarheeze\Compass\Authentication\AuthenticatedUser;

readonly class DashboardController
{
    public function __construct(
        private AlertFinder $alertFinder,
        private AuthenticatedUser $authenticatedUser,
        private IssueCounter $issueCounter,
        private ViewFactory $view,
    ) {
    }

    public function show(): View
    {
        return $this->view->make('compass::dashboard', [
            'alerts' => $this->alertFinder->find(),
            'issueCounts' => $this->issueCounter->count($this->authenticatedUser->user()),
        ]);
    }
}
