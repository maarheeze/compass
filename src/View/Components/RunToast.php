<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View\Components;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Maarheeze\Compass\Collector\RunStatus;
use Maarheeze\Compass\Models\Run;
use Maarheeze\Compass\View\ToastType;

class RunToast extends Component
{
    public function __construct(
        public readonly Run $run,
        private readonly ViewFactory $view,
    ) {
    }

    public function render(): View
    {
        return $this->view->make('compass::components.run-toast');
    }

    public function shouldRender(): bool
    {
        return $this->run->status !== RunStatus::QUEUED;
    }

    public function staysOpen(): bool
    {
        return $this->run->status === RunStatus::RUNNING;
    }

    public function toastType(): ToastType
    {
        return match ($this->run->status) {
            RunStatus::FAILED => ToastType::DANGER,
            RunStatus::QUEUED, RunStatus::RUNNING => ToastType::NEUTRAL,
            RunStatus::SUCCEEDED => ToastType::SUCCESS,
        };
    }
}
