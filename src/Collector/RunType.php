<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector;

enum RunType: string
{
    case FORGE_SYNC = 'forge-sync';
    case GITHUB_SYNC = 'github-sync';
}
