<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Http\Controllers;

use Illuminate\Contracts\Translation\Translator;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Maarheeze\Compass\Http\AssigneeFilter;
use Maarheeze\Compass\Http\Requests\AssigneeFilterRequest;
use Maarheeze\Compass\Models\PullRequest;

readonly class PullRequestController
{
    public function __construct(
        private Translator $translator,
        private ViewFactory $view,
    ) {
    }

    public function index(AssigneeFilterRequest $assigneeFilterRequest): View
    {
        $pullRequests = PullRequest::query()->listed();

        return $this->view->make('compass::pull-requests.index', [
            'assigneesFilter' => AssigneeFilter::assigneesFilter($pullRequests->assigneeFilterOptions($this->translator), $this->translator),
            'pullRequests' => $assigneeFilterRequest->assigneeFilter()->pullRequests($pullRequests),
        ]);
    }
}
