<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View\Components;

use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Maarheeze\Compass\Analyzer\GitHub\IssueCounts;
use Maarheeze\Compass\Http\RouteName;

class IssueCountsCard extends Component
{
    public readonly string $assignedUrl;
    public readonly string $openUrl;

    public function __construct(
        public readonly IssueCounts $issueCounts,
        UrlGenerator $url,
        private readonly ViewFactory $view,
    ) {
        $this->assignedUrl = $url->route(RouteName::GITHUB_ISSUES_ASSIGNED->value);
        $this->openUrl = $url->route(RouteName::GITHUB_ISSUES->value);
    }

    public function render(): View
    {
        return $this->view->make('compass::components.issue-counts-card');
    }
}
