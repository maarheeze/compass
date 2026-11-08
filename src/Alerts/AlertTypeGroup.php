<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts;

enum AlertTypeGroup: string
{
    case CODE_HOST = 'code_host';
    case COMPOSER = 'composer';
    case CUSTOM = 'custom';
    case DEPENDABOT = 'dependabot';
    case ENDPOINT = 'endpoint';
    case HOSTING_PROVIDER = 'hosting_provider';
    case SITE_REPORT = 'site_report';
}
