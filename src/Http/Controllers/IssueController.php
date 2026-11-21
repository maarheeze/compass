<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Http\Controllers;

use Illuminate\Contracts\Translation\Translator;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Maarheeze\Compass\Http\AssigneeFilter;
use Maarheeze\Compass\Http\ItemsPerPage;
use Maarheeze\Compass\Http\Requests\AssigneeFilterRequest;
use Maarheeze\Compass\Models\Issue;

readonly class IssueController
{
    public function __construct(
        private ItemsPerPage $itemsPerPage,
        private Translator $translator,
        private ViewFactory $view,
    ) {
    }

    public function index(AssigneeFilterRequest $assigneeFilterRequest): View
    {
        $issues = Issue::query()->listed();

        return $this->view->make('compass::issues.index', [
            'assigneesFilter' => AssigneeFilter::assigneesFilter($issues->assigneeFilterOptions($this->translator), $this->translator),
            'issues' => $assigneeFilterRequest
                ->assigneeFilter()
                ->issues(Issue::query())
                ->listedPage($this->itemsPerPage),
        ]);
    }
}
