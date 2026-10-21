<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Providers;

use Illuminate\Config\Repository;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use Laravel\Socialite\Two\GithubProvider;
use Maarheeze\Compass\Authentication\GitHubAuthentication;
use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\User;

use function sprintf;

class AuthenticationProvider extends ServiceProvider
{
    public const string GUARD = 'compass';
    private const string USER_PROVIDER = 'compass_users';

    /**
     * @throws BindingResolutionException
     */
    public function register(): void
    {
        $config = $this->app->make(Repository::class);

        $config->set(sprintf('auth.guards.%s', self::GUARD), [
            'driver' => 'session',
            'provider' => self::USER_PROVIDER,
        ]);

        $config->set(sprintf('auth.providers.%s', self::USER_PROVIDER), [
            'driver' => 'eloquent',
            'model' => User::class,
        ]);

        $this->app->bind(GithubProvider::class, static function (Application $app): GithubProvider {
            $config = $app->make(Repository::class);
            $url = $app->make(UrlGenerator::class);

            $github = new GithubProvider(
                $app->make(Request::class),
                $config->string('compass.authentication.github.client_id'),
                $config->string('compass.authentication.github.client_secret'),
                $url->route(RouteName::GITHUB_CALLBACK),
            );

            return $github->scopes([
                'read:org',
                'user:email',
            ]);
        });

        $this->app
            ->when(GitHubAuthentication::class)
            ->needs('$organization')
            ->giveConfig('compass.github_organization');
    }
}
