<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector;

enum MissingState
{
    case COLLECTED;
    case IGNORED;
    case MISSING;
}
