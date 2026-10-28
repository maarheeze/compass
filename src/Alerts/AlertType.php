<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts;

enum AlertType: string
{
    case DEBUG_ENABLED = 'debug-enabled';
    case DOWN_SITE_ENDPOINT = 'down-site-endpoint';
    case NON_PRODUCTION = 'non-production';
}
