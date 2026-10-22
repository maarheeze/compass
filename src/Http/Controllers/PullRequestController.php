<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Http\Controllers;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Maarheeze\Compass\Analyzer\GitHub\PullRequestFinder;
use Maarheeze\Compass\Authentication\AuthenticatedUser;
use Maarheeze\Compass\Http\RouteName;

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
            'activeRouteName' => RouteName::GITHUB_PULL_REQUESTS_ASSIGNED,
            'pullRequests' => $this->pullRequestFinder->findPullRequestsAssignedTo($this->authenticatedUser->user()),
        ]);
    }

    public function index(): View
    {
        return $this->view->make('compass::pull-requests.index', [
            'activeRouteName' => RouteName::GITHUB_PULL_REQUESTS,
            'pullRequests' => $this->pullRequestFinder->findPullRequests(),
        ]);
    }
}
