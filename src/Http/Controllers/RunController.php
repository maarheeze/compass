<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Http\Controllers;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Maarheeze\Compass\Collector\RunType;
use Maarheeze\Compass\Models\Run;

readonly class RunController
{
    public function __construct(
        private int $itemsPerPage,
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
        ]);
    }

    public function type(RunType $runType): View
    {
        return $this->view->make('compass::runs.index', [
            'runs' => Run::query()
                ->ofType($runType)
                ->with('failures')
                ->latest()
                ->paginate($this->itemsPerPage),
        ]);
    }
}
