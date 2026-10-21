<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View\Components;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Maarheeze\Compass\Authentication\AuthenticatedUser;
use Maarheeze\Compass\Models\User;

class Navigation extends Component
{
    public readonly User $user;

    public function __construct(
        AuthenticatedUser $authenticatedUser,
        private readonly ViewFactory $view,
    ) {
        $this->user = $authenticatedUser->user();
    }

    public function render(): View
    {
        return $this->view->make('compass::components.navigation');
    }
}
