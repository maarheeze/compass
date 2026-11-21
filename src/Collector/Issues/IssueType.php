<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Issues;

enum IssueType: string
{
    case BUG = 'bug';
    case FEATURE = 'feature';
    case TASK = 'task';
}
