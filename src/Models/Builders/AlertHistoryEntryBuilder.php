<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Models\Builders;

use Illuminate\Database\Eloquent\Builder;
use Maarheeze\Compass\Collections\AlertHistoryEntryCollection;
use Maarheeze\Compass\Models\AlertHistoryEntry;
use Webmozart\Assert\Assert;

/**
 * @extends Builder<AlertHistoryEntry>
 */
class AlertHistoryEntryBuilder extends Builder
{
    public function listed(): AlertHistoryEntryCollection
    {
        return Assert::isInstanceOf(
            $this
                ->with('user')
                ->latest('occurred_at')
                ->get(),
            AlertHistoryEntryCollection::class,
        );
    }
}
