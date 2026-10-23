<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Http;

enum RouteName: string
{
    case DASHBOARD = 'compass.dashboard';
    case FORGE_SITE = 'compass.forge.site';
    case FORGE_SITE_CLIENT_EDIT = 'compass.forge.site.client.edit';
    case FORGE_SITE_CLIENT_UPDATE = 'compass.forge.site.client.update';
    case FORGE_SITE_DELETE = 'compass.forge.site.delete';
    case FORGE_SITE_IGNORE = 'compass.forge.site.ignore';
    case FORGE_SITES = 'compass.forge.sites';
    case GITHUB_CALLBACK = 'compass.auth.github.callback';
    case GITHUB_ISSUES = 'compass.github.issues';
    case GITHUB_ISSUES_ASSIGNED = 'compass.github.issues.assigned';
    case GITHUB_PACKAGE = 'compass.github.package';
    case GITHUB_PULL_REQUESTS = 'compass.github.pull-requests';
    case GITHUB_PULL_REQUESTS_ASSIGNED = 'compass.github.pull-requests.assigned';
    case GITHUB_REDIRECT = 'compass.auth.github';
    case GITHUB_REPOSITORIES = 'compass.github.repositories';
    case GITHUB_REPOSITORY = 'compass.github.repository';
    case GITHUB_REPOSITORY_DELETE = 'compass.github.repository.delete';
    case GITHUB_REPOSITORY_IGNORE = 'compass.github.repository.ignore';
    case LOGIN = 'compass.login';
    case LOGOUT = 'compass.logout';
    case SYNC = 'compass.sync';
}
