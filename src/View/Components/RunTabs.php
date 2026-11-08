<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View\Components;

use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Contracts\Translation\Translator;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\View\Component;
use Maarheeze\Compass\Collections\NavigationTabCollection;
use Maarheeze\Compass\Collector\RunType;
use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\View\EnumLabels;
use Maarheeze\Compass\View\NavigationTab;
use Webmozart\Assert\Assert;

class RunTabs extends Component
{
    public readonly NavigationTabCollection $tabs;

    public function __construct(
        EnumLabels $enumLabels,
        Request $request,
        Translator $translator,
        UrlGenerator $url,
        private readonly ViewFactory $view,
    ) {
        $allRunsUrl = $url->route(RouteName::RUNS->value);

        $this->tabs = new NavigationTabCollection([
            new NavigationTab(
                $allRunsUrl,
                Assert::string($translator->get('compass::run.all_runs')),
                null,
                $request->url() === $allRunsUrl,
            ),
            $this->runTypeTab($enumLabels, $request, $translator, $url, RunType::GITHUB_SYNC),
            $this->runTypeTab($enumLabels, $request, $translator, $url, RunType::FORGE_SYNC),
            $this->runTypeTab($enumLabels, $request, $translator, $url, RunType::ENDPOINT_CHECK),
        ]);
    }

    public function render(): View
    {
        return $this->view->make('compass::components.run-tabs');
    }

    private function runTypeTab(
        EnumLabels $enumLabels,
        Request $request,
        Translator $translator,
        UrlGenerator $url,
        RunType $runType,
    ): NavigationTab {
        $runTypeUrl = $url->route(RouteName::RUNS_OF_TYPE->value, $runType);

        return new NavigationTab(
            $runTypeUrl,
            Assert::string($translator->get($enumLabels->labelKey($runType))),
            null,
            $request->url() === $runTypeUrl,
        );
    }
}
