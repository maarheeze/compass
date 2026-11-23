<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Http\Controllers;

use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Contracts\Translation\Translator;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Routing\Redirector;
use Maarheeze\Compass\Http\ItemsPerPage;
use Maarheeze\Compass\Http\Requests\RunScheduledCommandRequest;
use Maarheeze\Compass\Http\Requests\RunTypeFilterRequest;
use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Http\RunTypeFilter;
use Maarheeze\Compass\Models\Run;
use Maarheeze\Compass\Scheduling\ScheduledCommands;

readonly class RunController
{
    public function __construct(
        private ItemsPerPage $itemsPerPage,
        private Redirector $redirector,
        private ResponseFactory $responseFactory,
        private ScheduledCommands $scheduledCommands,
        private Translator $translator,
        private ViewFactory $view,
    ) {
    }

    public function index(RunTypeFilterRequest $runTypeFilterRequest): View
    {
        return $this->view->make('compass::runs.index', [
            'runs' => $runTypeFilterRequest
                ->runTypeFilter()
                ->runs(Run::query())
                ->listed($this->itemsPerPage),
            'runTypesFilter' => RunTypeFilter::runTypesFilter(
                Run::query()->runTypeFilterOptions($this->translator),
                $this->translator,
            ),
            'scheduledCommands' => $this->scheduledCommands->all(),
        ]);
    }

    public function run(RunScheduledCommandRequest $runScheduledCommandRequest): RedirectResponse|Response
    {
        $this->scheduledCommands->run($runScheduledCommandRequest->mutexName());

        if ($runScheduledCommandRequest->expectsJson()) {
            return $this->responseFactory->noContent(Response::HTTP_ACCEPTED);
        }

        return $this->redirector->route(RouteName::RUNS);
    }
}
