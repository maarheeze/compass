<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts;

enum AlertType: string
{
    case DOWN_SITE_ENDPOINT = 'down-site-endpoint';
}
