<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Forge;

enum SiteStatus: string
{
    case CREATING = 'creating';
    case DEPLOYED = 'deployed';
    case DEPLOYING = 'deploying';
    case FAILED = 'failed';
    case INSTALLED = 'installed';
    case INSTALLING = 'installing';
    case MAINTENANCE = 'maintenance';
    case NEVER_DEPLOYED = 'never-deployed';
    case REMOVING = 'removing';
    case UNINSTALLING = 'uninstalling';
}
