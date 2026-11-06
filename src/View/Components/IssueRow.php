<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View\Components;

use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\Issue;
use Maarheeze\Compass\Models\IssueAssignee;

class IssueRow extends Component
{
    public function __construct(
        public readonly Issue $issue,
        private readonly UrlGenerator $url,
        private readonly ViewFactory $view,
    ) {
    }

    public function assigneeUrl(IssueAssignee $assignee): string
    {
        return $this->url->route(RouteName::ISSUES->value, [
            'source' => $this->issue->source,
            'assignee' => $assignee->login,
        ]);
    }

    public function render(): View
    {
        return $this->view->make('compass::components.issue-row');
    }
}
