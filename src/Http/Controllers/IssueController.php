<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Http\Controllers;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Maarheeze\Compass\Http\Requests\AssigneeFilterRequest;
use Maarheeze\Compass\Models\Issue;

readonly class IssueController
{
    public function __construct(
        private ViewFactory $view,
    ) {
    }

    public function index(AssigneeFilterRequest $assigneeFilterRequest): View
    {
        $issueQuery = Issue::query()
            ->with(['assignees', 'repository'])
            ->latestOpened();

        $assigneeFilter = $assigneeFilterRequest->assigneeFilter();

        if ($assigneeFilter !== null) {
            $issueQuery->assignedTo($assigneeFilter->source, $assigneeFilter->login);
        }

        return $this->view->make('compass::issues.index', [
            'issues' => $issueQuery->get(),
        ]);
    }
}
