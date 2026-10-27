<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Http\Controllers;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Maarheeze\Compass\Analyzer\GitHub\PullRequestFinder;
use Maarheeze\Compass\Authentication\AuthenticatedUser;

readonly class PullRequestController
{
    public function __construct(
        private AuthenticatedUser $authenticatedUser,
        private PullRequestFinder $pullRequestFinder,
        private ViewFactory $view,
    ) {
    }

    public function assigned(): View
    {
        return $this->view->make('compass::pull-requests.index', [
            'pullRequests' => $this->pullRequestFinder->findPullRequestsAssignedTo($this->authenticatedUser->user()),
        ]);
    }

    public function index(): View
    {
        return $this->view->make('compass::pull-requests.index', [
            'pullRequests' => $this->pullRequestFinder->findPullRequests(),
        ]);
    }
}
