<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View\Components;

use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\View\Component;
use Maarheeze\Compass\Collections\NavigationTabCollection;
use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\View\NavigationTab;

class PullRequestTabs extends Component
{
    public readonly NavigationTabCollection $tabs;

    public function __construct(
        Request $request,
        UrlGenerator $url,
        private readonly ViewFactory $view,
    ) {
        $this->tabs = new NavigationTabCollection([
            new NavigationTab(
                $url->route(RouteName::GITHUB_PULL_REQUESTS->value),
                'All open',
                'compass::icons.git-pull-request',
                $request->routeIs(RouteName::GITHUB_PULL_REQUESTS->value),
            ),
            new NavigationTab(
                $url->route(RouteName::GITHUB_PULL_REQUESTS_ASSIGNED->value),
                'Assigned to me',
                'compass::icons.person',
                $request->routeIs(RouteName::GITHUB_PULL_REQUESTS_ASSIGNED->value),
            ),
        ]);
    }

    public function render(): View
    {
        return $this->view->make('compass::components.pull-request-tabs');
    }
}
