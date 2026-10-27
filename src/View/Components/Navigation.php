<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View\Components;

use Carbon\CarbonImmutable;
use Illuminate\Config\Repository;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Maarheeze\Compass\Authentication\AuthenticatedUser;
use Maarheeze\Compass\Collections\NavigationTabCollection;
use Maarheeze\Compass\Collector\LastCollection;
use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\User;
use Maarheeze\Compass\View\NavigationSection;
use Maarheeze\Compass\View\NavigationTab;

class Navigation extends Component
{
    public readonly string $applicationName;
    public readonly string $dashboardUrl;
    public readonly ?CarbonImmutable $lastCollectedAt;
    public readonly string $logoutUrl;
    public readonly string $syncUrl;
    public readonly NavigationTabCollection $tabs;
    public readonly User $user;

    public function __construct(
        AuthenticatedUser $authenticatedUser,
        Repository $config,
        LastCollection $lastCollection,
        NavigationSection $section,
        UrlGenerator $url,
        private readonly ViewFactory $view,
    ) {
        $this->applicationName = $config->string('app.name');
        $this->dashboardUrl = $url->route(RouteName::DASHBOARD->value);
        $this->lastCollectedAt = $lastCollection->collectedAt();
        $this->logoutUrl = $url->route(RouteName::LOGOUT->value);
        $this->syncUrl = $url->route(RouteName::SYNC->value);
        $this->tabs = $this->navigationTabs($url, $section);
        $this->user = $authenticatedUser->user();
    }

    public function render(): View
    {
        return $this->view->make('compass::components.navigation');
    }

    private function navigationTabs(UrlGenerator $url, NavigationSection $section): NavigationTabCollection
    {
        return new NavigationTabCollection([
            new NavigationTab(
                $url->route(RouteName::DASHBOARD->value),
                'Dashboard',
                'compass::icons.home',
                $section === NavigationSection::DASHBOARD,
            ),
            new NavigationTab(
                $url->route(RouteName::GITHUB_REPOSITORIES->value),
                'Repositories',
                'compass::icons.repo',
                $section === NavigationSection::REPOSITORIES,
            ),
            new NavigationTab(
                $url->route(RouteName::GITHUB_ISSUES->value),
                'Issues',
                'compass::icons.issue-opened',
                $section === NavigationSection::ISSUES,
            ),
            new NavigationTab(
                $url->route(RouteName::GITHUB_PULL_REQUESTS->value),
                'Pull requests',
                'compass::icons.git-pull-request',
                $section === NavigationSection::PULL_REQUESTS,
            ),
            new NavigationTab(
                $url->route(RouteName::FORGE_SITES->value),
                'Sites',
                'compass::icons.server',
                $section === NavigationSection::SITES,
            ),
            new NavigationTab(
                $url->route(RouteName::RUNS->value),
                'Runs',
                'compass::icons.sync',
                $section === NavigationSection::RUNS,
            ),
        ]);
    }
}
