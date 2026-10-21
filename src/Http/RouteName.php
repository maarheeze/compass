<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Http;

enum RouteName: string
{
    case DASHBOARD = 'compass.dashboard';
    case GITHUB_CALLBACK = 'compass.auth.github.callback';
    case GITHUB_REDIRECT = 'compass.auth.github';
    case GITHUB_REPOSITORIES = 'compass.github.repositories';
    case GITHUB_REPOSITORY = 'compass.github.repository';
    case GITHUB_SYNC = 'compass.github.sync';
    case LOGIN = 'compass.login';
    case LOGOUT = 'compass.logout';
}
