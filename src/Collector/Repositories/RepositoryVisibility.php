<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Repositories;

enum RepositoryVisibility: string
{
    case INTERNAL = 'INTERNAL';
    case PRIVATE = 'PRIVATE';
    case PUBLIC = 'PUBLIC';
}
