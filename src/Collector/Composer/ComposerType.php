<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Composer;

enum ComposerType: string
{
    case LIBRARY = 'library';
    case PROJECT = 'project';
}
