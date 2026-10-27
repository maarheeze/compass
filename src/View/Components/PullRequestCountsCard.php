<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View\Components;

use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Maarheeze\Compass\Analyzer\GitHub\PullRequestCounts;
use Maarheeze\Compass\Http\RouteName;

class PullRequestCountsCard extends Component
{
    public readonly string $assignedUrl;
    public readonly string $openUrl;

    public function __construct(
        public readonly PullRequestCounts $pullRequestCounts,
        UrlGenerator $url,
        private readonly ViewFactory $view,
    ) {
        $this->assignedUrl = $url->route(RouteName::GITHUB_PULL_REQUESTS_ASSIGNED->value);
        $this->openUrl = $url->route(RouteName::GITHUB_PULL_REQUESTS->value);
    }

    public function render(): View
    {
        return $this->view->make('compass::components.pull-request-counts-card');
    }
}
