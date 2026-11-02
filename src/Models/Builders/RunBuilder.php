<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Models\Builders;

use Illuminate\Database\Eloquent\Builder;
use Maarheeze\Compass\Collector\RunStatus;
use Maarheeze\Compass\Collector\RunType;
use Maarheeze\Compass\Models\Run;

/**
 * @extends Builder<Run>
 */
class RunBuilder extends Builder
{
    public function ofType(RunType $runType): self
    {
        return $this->where('type', $runType);
    }

    public function unfinished(): self
    {
        return $this->whereIn('status', [RunStatus::QUEUED, RunStatus::RUNNING]);
    }
}
