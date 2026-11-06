<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Http;

enum RouteName: string
{
    case ALERT = 'compass.alert';
    case ALERT_ASSIGN = 'compass.alert.assign';
    case ALERT_MARK_DONE = 'compass.alert.mark-done';
    case ALERT_MUTE = 'compass.alert.mute';
    case ALERT_REOPEN = 'compass.alert.reopen';
    case ALERT_UNASSIGN = 'compass.alert.unassign';
    case ALERT_UNMUTE = 'compass.alert.unmute';
    case DASHBOARD = 'compass.dashboard';
    case FORGE_SITE = 'compass.forge.site';
    case FORGE_SITE_DELETE = 'compass.forge.site.delete';
    case FORGE_SITE_ENDPOINT_CHECKS = 'compass.forge.site.endpoint-checks';
    case FORGE_SITE_ENDPOINT_CHECKS_DELETE = 'compass.forge.site.endpoint-checks.delete';
    case FORGE_SITE_IGNORE = 'compass.forge.site.ignore';
    case FORGE_SITE_REPORT_EDIT = 'compass.forge.site.report.edit';
    case FORGE_SITE_REPORT_UPDATE = 'compass.forge.site.report.update';
    case FORGE_SITES = 'compass.forge.sites';
    case GITHUB_CALLBACK = 'compass.auth.github.callback';
    case GITHUB_ISSUES = 'compass.github.issues';
    case GITHUB_PACKAGE = 'compass.github.package';
    case GITHUB_PULL_REQUESTS = 'compass.github.pull-requests';
    case GITHUB_REDIRECT = 'compass.auth.github';
    case GITHUB_REPOSITORIES = 'compass.github.repositories';
    case GITHUB_REPOSITORY = 'compass.github.repository';
    case GITHUB_REPOSITORY_DELETE = 'compass.github.repository.delete';
    case GITHUB_REPOSITORY_IGNORE = 'compass.github.repository.ignore';
    case LOGIN = 'compass.login';
    case LOGOUT = 'compass.logout';
    case RUNS = 'compass.runs';
    case RUNS_OF_TYPE = 'compass.runs.type';
    case SETTINGS_EDIT = 'compass.settings.edit';
    case SETTINGS_UPDATE = 'compass.settings.update';
}
