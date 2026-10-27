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

class IssueTabs extends Component
{
    public readonly NavigationTabCollection $tabs;

    public function __construct(
        Request $request,
        UrlGenerator $url,
        private readonly ViewFactory $view,
    ) {
        $this->tabs = new NavigationTabCollection([
            new NavigationTab(
                $url->route(RouteName::GITHUB_ISSUES->value),
                'compass::filter.all_open',
                'compass::icons.issue-opened',
                $request->routeIs(RouteName::GITHUB_ISSUES->value),
            ),
            new NavigationTab(
                $url->route(RouteName::GITHUB_ISSUES_ASSIGNED->value),
                'compass::filter.assigned_to_me',
                'compass::icons.person',
                $request->routeIs(RouteName::GITHUB_ISSUES_ASSIGNED->value),
            ),
        ]);
    }

    public function render(): View
    {
        return $this->view->make('compass::components.issue-tabs');
    }
}
