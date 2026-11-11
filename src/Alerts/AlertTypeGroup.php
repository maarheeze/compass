<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts;

use Maarheeze\Compass\View\LabelledEnum;

use function sprintf;

enum AlertTypeGroup: string implements LabelledEnum
{
    case CODE_HOST = 'code_host';
    case COMPOSER = 'composer';
    case CUSTOM = 'custom';
    case DEPENDABOT = 'dependabot';
    case ENDPOINT = 'endpoint';
    case HOSTING_PROVIDER = 'hosting_provider';
    case SITE_REPORT = 'site_report';

    public function labelKey(): string
    {
        return sprintf('compass::alert-type-group.%s', $this->name);
    }
}
