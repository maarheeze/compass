<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View\Components;

use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\View\Component;
use Maarheeze\Compass\Collections\NavigationTabCollection;
use Maarheeze\Compass\Collector\RunType;
use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\View\NavigationTab;

use function sprintf;

class RunTabs extends Component
{
    public readonly NavigationTabCollection $tabs;

    public function __construct(
        Request $request,
        UrlGenerator $url,
        private readonly ViewFactory $view,
    ) {
        $allRunsUrl = $url->route(RouteName::RUNS->value);

        $this->tabs = new NavigationTabCollection([
            new NavigationTab($allRunsUrl, 'compass::run.all_runs', null, $request->url() === $allRunsUrl),
            $this->runTypeTab($request, $url, RunType::GITHUB_SYNC),
            $this->runTypeTab($request, $url, RunType::FORGE_SYNC),
            $this->runTypeTab($request, $url, RunType::ENDPOINT_CHECK),
        ]);
    }

    public function render(): View
    {
        return $this->view->make('compass::components.run-tabs');
    }

    private function runTypeTab(Request $request, UrlGenerator $url, RunType $runType): NavigationTab
    {
        $runTypeUrl = $url->route(RouteName::RUNS_OF_TYPE->value, $runType);

        return new NavigationTab(
            $runTypeUrl,
            sprintf('compass::run-type.%s', $runType->name),
            null,
            $request->url() === $runTypeUrl,
        );
    }
}
