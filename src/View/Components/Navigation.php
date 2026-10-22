<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View\Components;

use Illuminate\Config\Repository;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Illuminate\View\Component;
use Maarheeze\Compass\Authentication\AuthenticatedUser;
use Maarheeze\Compass\Collector\LastCollection;
use Maarheeze\Compass\Models\User;
use Maarheeze\Compass\View\NavigationSection;

class Navigation extends Component
{
    public readonly string $applicationName;
    public readonly ?Carbon $lastCollectedAt;
    public readonly User $user;

    public function __construct(
        AuthenticatedUser $authenticatedUser,
        Repository $config,
        LastCollection $lastCollection,
        public readonly NavigationSection $section,
        private readonly ViewFactory $view,
    ) {
        $this->applicationName = $config->string('app.name');
        $this->lastCollectedAt = $lastCollection->collectedAt();
        $this->user = $authenticatedUser->user();
    }

    public function render(): View
    {
        return $this->view->make('compass::components.navigation');
    }
}
