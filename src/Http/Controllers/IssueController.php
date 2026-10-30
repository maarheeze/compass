<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Http\Controllers;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Maarheeze\Compass\Authentication\AuthenticatedUser;
use Maarheeze\Compass\Models\Issue;

readonly class IssueController
{
    public function __construct(
        private AuthenticatedUser $authenticatedUser,
        private ViewFactory $view,
    ) {
    }

    public function assigned(): View
    {
        return $this->view->make('compass::issues.index', [
            'issues' => Issue::query()
                ->with(['assignees', 'repository'])
                ->assignedTo($this->authenticatedUser->user())
                ->latestOpened()
                ->get(),
        ]);
    }

    public function index(): View
    {
        return $this->view->make('compass::issues.index', [
            'issues' => Issue::query()
                ->with(['assignees', 'repository'])
                ->latestOpened()
                ->get(),
        ]);
    }
}
