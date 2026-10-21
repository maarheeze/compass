<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Analyzer\Composer;

enum ComposerWarning
{
    case LIBRARY_WITH_LOCK;
    case MISSING_TYPE;
    case PROJECT_WITHOUT_LOCK;
    case UNKNOWN_TYPE;
}
