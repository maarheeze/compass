<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Models\Builders;

use Illuminate\Database\Eloquent\Builder;
use Maarheeze\Compass\Models\ComposerJsonPackage;

/**
 * @extends Builder<ComposerJsonPackage>
 */
class ComposerJsonPackageBuilder extends Builder
{
    public function named(string $packageName): self
    {
        return $this->where('name', $packageName);
    }
}
