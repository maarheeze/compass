<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Analyzer\Composer;

enum RepositoryKind
{
    case LIBRARY;
    case OTHER;
    case PROJECT;
}
