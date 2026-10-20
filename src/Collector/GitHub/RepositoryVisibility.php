<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\GitHub;

enum RepositoryVisibility: string
{
    case INTERNAL = 'INTERNAL';
    case PRIVATE = 'PRIVATE';
    case PUBLIC = 'PUBLIC';
}
