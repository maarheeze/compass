<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Models\Builders;

use Illuminate\Database\Eloquent\Builder;
use Maarheeze\Compass\Models\Repository;

/**
 * @extends Builder<Repository>
 */
class RepositoryBuilder extends Builder
{
    public function collected(): self
    {
        return $this->whereNull('missing_at');
    }

    public function missing(): self
    {
        return $this
            ->whereNotNull('missing_at')
            ->whereNull('missing_ignored_at');
    }

    public function notCollected(): self
    {
        return $this->whereNotNull('missing_at');
    }
}
