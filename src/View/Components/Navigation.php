<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View\Components;

use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\View\Component;
use Maarheeze\Compass\Authentication\AuthenticatedUser;
use Maarheeze\Compass\Collections\NavigationTabCollection;
use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\Alert;
use Maarheeze\Compass\Models\User;
use Maarheeze\Compass\View\NavigationTab;

class Navigation extends Component
{
    public readonly string $dashboardUrl;
    public readonly bool $hasAlerts;
    public readonly string $logoutUrl;
    public readonly NavigationTabCollection $tabs;
    public readonly User $user;

    public function __construct(
        AuthenticatedUser $authenticatedUser,
        Request $request,
        UrlGenerator $url,
        private readonly ViewFactory $view,
    ) {
        $this->dashboardUrl = $url->route(RouteName::DASHBOARD->value);
        $this->hasAlerts = Alert::query()->notDone()->exists();
        $this->logoutUrl = $url->route(RouteName::LOGOUT->value);
        $this->tabs = $this->navigationTabs($request, $url);
        $this->user = $authenticatedUser->user();
    }

    public function render(): View
    {
        return $this->view->make('compass::components.navigation');
    }

    private function navigationTabs(Request $request, UrlGenerator $url): NavigationTabCollection
    {
        return new NavigationTabCollection([
            new NavigationTab(
                $url->route(RouteName::DASHBOARD->value),
                'compass::navigation.dashboard',
                'compass::icons.home',
                $request->routeIs(RouteName::DASHBOARD->value),
            ),
            new NavigationTab(
                $url->route(RouteName::GITHUB_REPOSITORIES->value),
                'compass::navigation.repositories',
                'compass::icons.repo',
                $request->routeIs(
                    RouteName::GITHUB_REPOSITORIES->value,
                    RouteName::GITHUB_REPOSITORY->value,
                    RouteName::GITHUB_PACKAGE->value,
                ),
            ),
            new NavigationTab(
                $url->route(RouteName::GITHUB_ISSUES->value),
                'compass::navigation.issues',
                'compass::icons.issue-opened',
                $request->routeIs(
                    RouteName::GITHUB_ISSUES->value,
                    RouteName::GITHUB_ISSUES_ASSIGNED->value,
                ),
            ),
            new NavigationTab(
                $url->route(RouteName::GITHUB_PULL_REQUESTS->value),
                'compass::navigation.pull_requests',
                'compass::icons.git-pull-request',
                $request->routeIs(
                    RouteName::GITHUB_PULL_REQUESTS->value,
                    RouteName::GITHUB_PULL_REQUESTS_ASSIGNED->value,
                ),
            ),
            new NavigationTab(
                $url->route(RouteName::FORGE_SITES->value),
                'compass::navigation.sites',
                'compass::icons.server',
                $request->routeIs(
                    RouteName::FORGE_SITES->value,
                    RouteName::FORGE_SITE->value,
                    RouteName::FORGE_SITE_ENDPOINT_CHECKS->value,
                    RouteName::FORGE_SITE_CLIENT_EDIT->value,
                ),
            ),
        ]);
    }
}
