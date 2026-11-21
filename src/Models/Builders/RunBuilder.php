<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Models\Builders;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Maarheeze\Compass\Http\ItemsPerPage;
use Maarheeze\Compass\Models\Run;

/**
 * @extends Builder<Run>
 */
class RunBuilder extends Builder
{
    /**
     * @return LengthAwarePaginator<int, Run>
     */
    public function listed(ItemsPerPage $itemsPerPage): LengthAwarePaginator
    {
        return $this
            ->with('failures')
            ->latest()
            ->paginate($itemsPerPage->value);
    }
}
