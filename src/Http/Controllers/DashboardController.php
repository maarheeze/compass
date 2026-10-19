<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Http\Controllers;

use Illuminate\Contracts\Auth\Factory as AuthenticationFactory;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Maarheeze\Compass\Models\Project;
use Maarheeze\Compass\Models\User;
use Maarheeze\Compass\Providers\AuthenticationProvider;
use Webmozart\Assert\Assert;

readonly class DashboardController
{
    public function __construct(
        private AuthenticationFactory $authentication,
        private ViewFactory $view,
    ) {
    }

    public function show(): View
    {
        $user = $this->authentication->guard(AuthenticationProvider::GUARD)->user();
        Assert::isInstanceOf($user, User::class);

        return $this->view->make('compass::dashboard', [
            'projects' => Project::query()->orderBy('repository')->get(),
            'user' => $user,
        ]);
    }
}
