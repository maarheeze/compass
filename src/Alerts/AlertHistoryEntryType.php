<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts;

enum AlertHistoryEntryType: string
{
    case MARKED_DONE = 'marked-done';
    case MUTED = 'muted';
    case OPENED = 'opened';
    case RECURRED = 'recurred';
    case REOPENED = 'reopened';
    case RESOLVED = 'resolved';
    case UNMUTED = 'unmuted';
}
