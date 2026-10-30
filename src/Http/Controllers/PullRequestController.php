<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Http\Controllers;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Maarheeze\Compass\Authentication\AuthenticatedUser;
use Maarheeze\Compass\Models\PullRequest;

readonly class PullRequestController
{
    public function __construct(
        private AuthenticatedUser $authenticatedUser,
        private ViewFactory $view,
    ) {
    }

    public function assigned(): View
    {
        return $this->view->make('compass::pull-requests.index', [
            'pullRequests' => PullRequest::query()
                ->with(['assignees', 'repository'])
                ->assignedTo($this->authenticatedUser->user())
                ->latestOpened()
                ->get(),
        ]);
    }

    public function index(): View
    {
        return $this->view->make('compass::pull-requests.index', [
            'pullRequests' => PullRequest::query()
                ->with(['assignees', 'repository'])
                ->latestOpened()
                ->get(),
        ]);
    }
}
