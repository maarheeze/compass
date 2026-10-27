<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Livewire;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Maarheeze\Compass\Collector\LastCollection;
use Maarheeze\Compass\Collector\SyncStatus;
use Maarheeze\Compass\Console\SyncCommand;
use Maarheeze\Compass\Http\RouteName;

class SyncButton extends Component
{
    public function render(
        LastCollection $lastCollection,
        SyncStatus $syncStatus,
        UrlGenerator $url,
        ViewFactory $view,
    ): View {
        return $view->make('compass::livewire.sync-button', [
            'isSyncing' => $syncStatus->isRunning(),
            'lastCollectedAt' => $lastCollection->collectedAt(),
            'runsUrl' => $url->route(RouteName::RUNS->value),
        ]);
    }

    public function sync(Kernel $kernel): void
    {
        $kernel->call(SyncCommand::SIGNATURE);
    }
}
