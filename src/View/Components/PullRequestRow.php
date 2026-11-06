<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View\Components;

use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\PullRequest;
use Maarheeze\Compass\Models\PullRequestAssignee;

class PullRequestRow extends Component
{
    public function __construct(
        public readonly PullRequest $pullRequest,
        private readonly UrlGenerator $url,
        private readonly ViewFactory $view,
    ) {
    }

    public function assigneeUrl(PullRequestAssignee $assignee): string
    {
        return $this->url->route(RouteName::PULL_REQUESTS->value, [
            'source' => $this->pullRequest->source,
            'assignee' => $assignee->login,
        ]);
    }

    public function render(): View
    {
        return $this->view->make('compass::components.pull-request-row');
    }
}
