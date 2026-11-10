<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Http\Controllers;

use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Routing\Redirector;
use Maarheeze\Compass\Console\ScheduledCommands;
use Maarheeze\Compass\Http\Requests\RunScheduledCommandRequest;
use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\Run;

readonly class RunController
{
    public function __construct(
        private int $itemsPerPage,
        private Redirector $redirector,
        private ResponseFactory $responseFactory,
        private ScheduledCommands $scheduledCommands,
        private ViewFactory $view,
    ) {
    }

    public function index(): View
    {
        return $this->view->make('compass::runs.index', [
            'runs' => Run::query()
                ->with('failures')
                ->latest()
                ->paginate($this->itemsPerPage),
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
