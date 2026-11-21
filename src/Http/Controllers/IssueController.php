<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Http\Controllers;

use Illuminate\Contracts\Translation\Translator;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Maarheeze\Compass\Http\AssigneeFilter;
use Maarheeze\Compass\Http\IssueTypeFilter;
use Maarheeze\Compass\Http\ItemsPerPage;
use Maarheeze\Compass\Http\Requests\AssigneeFilterRequest;
use Maarheeze\Compass\Http\Requests\IssueTypeFilterRequest;
use Maarheeze\Compass\Models\Issue;

readonly class IssueController
{
    public function __construct(
        private ItemsPerPage $itemsPerPage,
        private Translator $translator,
        private ViewFactory $view,
    ) {
    }

    public function index(
        AssigneeFilterRequest $assigneeFilterRequest,
        IssueTypeFilterRequest $issueTypeFilterRequest,
    ): View {
        $issues = Issue::query()->listed();
        $issuesOfAssignee = $assigneeFilterRequest
            ->assigneeFilter()
            ->issues(Issue::query());

        return $this->view->make('compass::issues.index', [
            'assigneesFilter' => AssigneeFilter::assigneesFilter($issues->assigneeFilterOptions($this->translator), $this->translator),
            'issueTypesFilter' => IssueTypeFilter::issueTypesFilter(
                $issues->issueTypeFilterOptions($this->translator),
                $this->translator,
            ),
            'issues' => $issueTypeFilterRequest
                ->issueTypeFilter()
                ->issues($issuesOfAssignee)
                ->listedPage($this->itemsPerPage),
        ]);
    }
}
