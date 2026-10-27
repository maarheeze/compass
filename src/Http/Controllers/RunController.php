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
        private ViewFactory $view,
    ) {
    }

    public function index(): View
    {
        return $this->view->make('compass::runs.index', [
            'activeRunType' => null,
            'runs' => Run::query()->latest()->paginate(50),
        ]);
    }

    public function type(RunType $runType): View
    {
        return $this->view->make('compass::runs.index', [
            'activeRunType' => $runType,
            'runs' => Run::query()
                ->where('type', $runType)
                ->latest()
                ->paginate(50),
        ]);
    }
}
