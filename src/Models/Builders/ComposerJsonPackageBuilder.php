<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Models\Builders;

use Illuminate\Database\Eloquent\Builder;
use Maarheeze\Compass\Collections\ComposerJsonPackageCollection;
use Maarheeze\Compass\Collector\Composer\PackageSection;
use Maarheeze\Compass\Models\ComposerJsonPackage;
use Maarheeze\Compass\Models\Repository;
use Webmozart\Assert\Assert;

/**
 * @extends Builder<ComposerJsonPackage>
 */
class ComposerJsonPackageBuilder extends Builder
{
    public function development(): self
    {
        return $this->where('section', PackageSection::DEVELOPMENT);
    }

    public function listed(): ComposerJsonPackageCollection
    {
        return Assert::isInstanceOf(
            $this->orderBy('name')->get(),
            ComposerJsonPackageCollection::class,
        );
    }

    public function named(string $packageName): self
    {
        return $this->where('name', $packageName);
    }

    public function ofRepository(Repository $repository): self
    {
        return $this->whereRelation('composerJson', 'repository_id', $repository->getKey());
    }

    public function production(): self
    {
        return $this->where('section', PackageSection::PRODUCTION);
    }
}
