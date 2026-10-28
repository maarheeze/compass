<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts;

enum AlertHistoryEntryType: string
{
    case CLEARED = 'cleared';
    case MARKED_DONE = 'marked-done';
    case OPENED = 'opened';
    case RECURRED = 'recurred';
}
