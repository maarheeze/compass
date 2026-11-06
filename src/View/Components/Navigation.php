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
    public readonly bool $hasAlertsNotMuted;
    public readonly string $logoutUrl;
    public readonly string $settingsUrl;
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
        $this->hasAlertsNotMuted = Alert::query()
            ->notDone()
            ->notMuted()
            ->exists();
        $this->logoutUrl = $url->route(RouteName::LOGOUT->value);
        $this->settingsUrl = $url->route(RouteName::SETTINGS_EDIT->value);
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
                $url->route(RouteName::REPOSITORIES->value),
                'compass::navigation.repositories',
                'compass::icons.repo',
                $request->routeIs(
                    RouteName::REPOSITORIES->value,
                    RouteName::REPOSITORY->value,
                    RouteName::PACKAGE->value,
                ),
            ),
            new NavigationTab(
                $url->route(RouteName::ISSUES->value),
                'compass::navigation.issues',
                'compass::icons.issue-opened',
                $request->routeIs(RouteName::ISSUES->value),
            ),
            new NavigationTab(
                $url->route(RouteName::PULL_REQUESTS->value),
                'compass::navigation.pull_requests',
                'compass::icons.git-pull-request',
                $request->routeIs(RouteName::PULL_REQUESTS->value),
            ),
            new NavigationTab(
                $url->route(RouteName::SITES->value),
                'compass::navigation.sites',
                'compass::icons.server',
                $request->routeIs(
                    RouteName::SITES->value,
                    RouteName::SITE->value,
                    RouteName::SITE_ENDPOINT_CHECKS->value,
                    RouteName::SITE_REPORT_EDIT->value,
                ),
            ),
        ]);
    }
}
