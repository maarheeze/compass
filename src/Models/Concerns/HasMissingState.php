<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Models\Concerns;

use Maarheeze\Compass\Collector\MissingState;

trait HasMissingState
{
    public function missingState(): MissingState
    {
        if ($this->missing_at === null) {
            return MissingState::COLLECTED;
        }

        if ($this->missing_ignored_at === null) {
            return MissingState::MISSING;
        }

        return MissingState::IGNORED;
    }
}
