<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Http;

enum RouteName: string
{
    case DASHBOARD = 'compass.dashboard';
    case FORGE_SITE = 'compass.forge.site';
    case FORGE_SITES = 'compass.forge.sites';
    case FORGE_SYNC = 'compass.forge.sync';
    case GITHUB_CALLBACK = 'compass.auth.github.callback';
    case GITHUB_ISSUES = 'compass.github.issues';
    case GITHUB_ISSUES_ASSIGNED = 'compass.github.issues.assigned';
    case GITHUB_PACKAGE = 'compass.github.package';
    case GITHUB_PULL_REQUESTS = 'compass.github.pull-requests';
    case GITHUB_PULL_REQUESTS_ASSIGNED = 'compass.github.pull-requests.assigned';
    case GITHUB_REDIRECT = 'compass.auth.github';
    case GITHUB_REPOSITORIES = 'compass.github.repositories';
    case GITHUB_REPOSITORY = 'compass.github.repository';
    case GITHUB_SYNC = 'compass.github.sync';
    case LOGIN = 'compass.login';
    case LOGOUT = 'compass.logout';
}
