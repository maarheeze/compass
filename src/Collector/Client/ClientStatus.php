<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Client;

enum ClientStatus: string
{
    case INSTALLED = 'installed';
    case NOT_INSTALLED = 'not-installed';
    case UNAUTHORIZED = 'unauthorized';
    case UNREACHABLE = 'unreachable';
}
