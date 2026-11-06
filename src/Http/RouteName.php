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
    case GITHUB_CALLBACK = 'compass.auth.github.callback';
    case GITHUB_REDIRECT = 'compass.auth.github';
    case ISSUES = 'compass.issues';
    case LOGIN = 'compass.login';
    case LOGOUT = 'compass.logout';
    case PACKAGE = 'compass.package';
    case PULL_REQUESTS = 'compass.pull-requests';
    case REPOSITORIES = 'compass.repositories';
    case REPOSITORY = 'compass.repository';
    case REPOSITORY_DELETE = 'compass.repository.delete';
    case REPOSITORY_IGNORE = 'compass.repository.ignore';
    case RUNS = 'compass.runs';
    case RUNS_OF_TYPE = 'compass.runs.type';
    case SETTINGS_EDIT = 'compass.settings.edit';
    case SETTINGS_UPDATE = 'compass.settings.update';
    case SITE = 'compass.site';
    case SITES = 'compass.sites';
    case SITE_DELETE = 'compass.site.delete';
    case SITE_ENDPOINT_CHECKS = 'compass.site.endpoint-checks';
    case SITE_ENDPOINT_CHECKS_DELETE = 'compass.site.endpoint-checks.delete';
    case SITE_IGNORE = 'compass.site.ignore';
    case SITE_REPORT_EDIT = 'compass.site.report.edit';
    case SITE_REPORT_UPDATE = 'compass.site.report.update';
}
