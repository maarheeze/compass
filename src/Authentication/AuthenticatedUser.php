<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Authentication;

use Illuminate\Contracts\Auth\Factory as AuthenticationFactory;
use Maarheeze\Compass\Models\User;
use Maarheeze\Compass\Providers\AuthenticationProvider;
use Webmozart\Assert\Assert;

readonly class AuthenticatedUser
{
    public function __construct(
        private AuthenticationFactory $authentication,
    ) {
    }

    public function user(): User
    {
        $user = $this->authentication->guard(AuthenticationProvider::GUARD)->user();
        Assert::isInstanceOf($user, User::class);

        return $user;
    }
}
