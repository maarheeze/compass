<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts;

use Maarheeze\Compass\View\LabelledEnum;

use function sprintf;

enum AlertHistoryEntryType: string implements LabelledEnum
{
    case ASSIGNED = 'assigned';
    case MARKED_DONE = 'marked-done';
    case MUTED = 'muted';
    case OPENED = 'opened';
    case RECURRED = 'recurred';
    case REOPENED = 'reopened';
    case RESOLVED = 'resolved';
    case UNASSIGNED = 'unassigned';
    case UNMUTED = 'unmuted';

    public function labelKey(): string
    {
        return sprintf('compass::alert-history-entry-type.%s', $this->name);
    }
}
