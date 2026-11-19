<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Models\Builders;

use Illuminate\Database\Eloquent\Builder;
use Maarheeze\Compass\Collections\ComposerLockPackageCollection;
use Maarheeze\Compass\Models\ComposerLockPackage;
use Maarheeze\Compass\Models\Repository;
use Webmozart\Assert\Assert;

/**
 * @extends Builder<ComposerLockPackage>
 */
class ComposerLockPackageBuilder extends Builder
{
    public function listed(): ComposerLockPackageCollection
    {
        return Assert::isInstanceOf(
            $this->orderBy('name')->get(),
            ComposerLockPackageCollection::class,
        );
    }

    public function named(string $packageName): self
    {
        return $this->where('name', $packageName);
    }

    public function ofRepository(Repository $repository): self
    {
        return $this->whereRelation('composerLock', 'repository_id', $repository->getKey());
    }

    public function versioned(string $version): self
    {
        return $this->where('version', $version);
    }
}
