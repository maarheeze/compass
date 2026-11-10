<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View\Components;

use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Maarheeze\Compass\Http\AssigneeFilter;
use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\Issue;
use Maarheeze\Compass\Models\IssueAssignee;

class IssueRow extends Component
{
    public function __construct(
        public readonly Issue $issue,
        private readonly UrlGenerator $urlGenerator,
        private readonly ViewFactory $view,
    ) {
    }

    public function assigneeUrl(IssueAssignee $assignee): string
    {
        $assigneeFilter = new AssigneeFilter($this->issue->source, $assignee->login);

        return $this->urlGenerator->route(RouteName::ISSUES->value, $assigneeFilter->routeParameters());
    }

    public function render(): View
    {
        return $this->view->make('compass::components.issue-row');
    }
}
