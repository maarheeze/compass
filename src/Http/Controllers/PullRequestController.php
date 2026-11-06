<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Http\Controllers;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Maarheeze\Compass\Http\Requests\AssigneeFilterRequest;
use Maarheeze\Compass\Models\PullRequest;

readonly class PullRequestController
{
    public function __construct(
        private ViewFactory $view,
    ) {
    }

    public function index(AssigneeFilterRequest $assigneeFilterRequest): View
    {
        $pullRequestQuery = PullRequest::query()
            ->with(['assignees', 'repository'])
            ->latestOpened();

        $assigneeFilter = $assigneeFilterRequest->assigneeFilter();

        if ($assigneeFilter !== null) {
            $pullRequestQuery->assignedTo($assigneeFilter->source, $assigneeFilter->login);
        }

        return $this->view->make('compass::pull-requests.index', [
            'pullRequests' => $pullRequestQuery->get(),
        ]);
    }
}
