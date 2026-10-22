<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Http\Controllers;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Maarheeze\Compass\Analyzer\GitHub\IssueFinder;
use Maarheeze\Compass\Authentication\AuthenticatedUser;
use Maarheeze\Compass\Http\RouteName;

readonly class IssueController
{
    public function __construct(
        private AuthenticatedUser $authenticatedUser,
        private IssueFinder $issueFinder,
        private ViewFactory $view,
    ) {
    }

    public function assigned(): View
    {
        return $this->view->make('compass::issues.index', [
            'activeRouteName' => RouteName::GITHUB_ISSUES_ASSIGNED,
            'issues' => $this->issueFinder->findIssuesAssignedTo($this->authenticatedUser->user()),
        ]);
    }

    public function index(): View
    {
        return $this->view->make('compass::issues.index', [
            'activeRouteName' => RouteName::GITHUB_ISSUES,
            'issues' => $this->issueFinder->findIssues(),
        ]);
    }
}
