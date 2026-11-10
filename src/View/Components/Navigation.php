<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View\Components;

use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Contracts\Translation\Translator;
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
use Webmozart\Assert\Assert;

class Navigation extends Component
{
    public readonly string $dashboardUrl;
    public readonly NavigationTabCollection $dataTabs;
    public readonly bool $hasAlerts;
    public readonly bool $hasAlertsNotMuted;
    public readonly string $logoutUrl;
    public readonly NavigationTabCollection $operationTabs;
    public readonly string $settingsUrl;
    public readonly User $user;

    public function __construct(
        AuthenticatedUser $authenticatedUser,
        Request $request,
        Translator $translator,
        UrlGenerator $urlGenerator,
        private readonly ViewFactory $view,
    ) {
        $this->dashboardUrl = $urlGenerator->route(RouteName::DASHBOARD->value);
        $this->dataTabs = $this->dataNavigationTabs($request, $translator, $urlGenerator);
        $this->hasAlerts = Alert::query()->notDone()->exists();
        $this->hasAlertsNotMuted = Alert::query()
            ->notDone()
            ->notMuted()
            ->exists();
        $this->logoutUrl = $urlGenerator->route(RouteName::LOGOUT->value);
        $this->operationTabs = $this->operationNavigationTabs($request, $translator, $urlGenerator);
        $this->settingsUrl = $urlGenerator->route(RouteName::SETTINGS_EDIT->value);
        $this->user = $authenticatedUser->user();
    }

    public function render(): View
    {
        return $this->view->make('compass::components.navigation');
    }

    private function dataNavigationTabs(
        Request $request,
        Translator $translator,
        UrlGenerator $urlGenerator,
    ): NavigationTabCollection {
        return new NavigationTabCollection([
            new NavigationTab(
                $urlGenerator->route(RouteName::DASHBOARD->value),
                Assert::string($translator->get('compass::navigation.dashboard')),
                'compass::icons.home',
                $request->routeIs(RouteName::DASHBOARD->value),
            ),
            new NavigationTab(
                $urlGenerator->route(RouteName::REPOSITORIES->value),
                Assert::string($translator->get('compass::navigation.repositories')),
                'compass::icons.repo',
                $request->routeIs(
                    RouteName::REPOSITORIES->value,
                    RouteName::REPOSITORY->value,
                    RouteName::PACKAGE->value,
                ),
            ),
            new NavigationTab(
                $urlGenerator->route(RouteName::ISSUES->value),
                Assert::string($translator->get('compass::navigation.issues')),
                'compass::icons.issue-opened',
                $request->routeIs(RouteName::ISSUES->value),
            ),
            new NavigationTab(
                $urlGenerator->route(RouteName::PULL_REQUESTS->value),
                Assert::string($translator->get('compass::navigation.pull_requests')),
                'compass::icons.git-pull-request',
                $request->routeIs(RouteName::PULL_REQUESTS->value),
            ),
            new NavigationTab(
                $urlGenerator->route(RouteName::SITES->value),
                Assert::string($translator->get('compass::navigation.sites')),
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

    private function operationNavigationTabs(
        Request $request,
        Translator $translator,
        UrlGenerator $urlGenerator,
    ): NavigationTabCollection {
        return new NavigationTabCollection([
            new NavigationTab(
                $urlGenerator->route(RouteName::RUNS->value),
                Assert::string($translator->get('compass::navigation.runs')),
                'compass::icons.history',
                $request->routeIs(RouteName::RUNS->value),
            ),
        ]);
    }
}
