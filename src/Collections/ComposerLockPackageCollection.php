<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collections;

use Composer\Semver\Semver;
use Illuminate\Database\Eloquent\Collection;
use Maarheeze\Compass\Analyser\Composer\PackageVersionCount;
use Maarheeze\Compass\Models\ComposerLockPackage;
use Webmozart\Assert\Assert;

/**
 * @extends Collection<array-key, ComposerLockPackage>
 */
class ComposerLockPackageCollection extends Collection
{
    public function versionCounts(): PackageVersionCountCollection
    {
        $lockedPackageCountsByVersion = $this->countBy(static function (ComposerLockPackage $lockedPackage): string {
            return $lockedPackage->version;
        });
        $versions = $this
            ->map(static function (ComposerLockPackage $lockedPackage): string {
                return $lockedPackage->version;
            })
            ->unique()
            ->all();
        $packageVersionCounts = new PackageVersionCountCollection();

        foreach (Semver::sort($versions) as $version) {
            $packageVersionCounts->push(new PackageVersionCount(
                $version,
                Assert::integer($lockedPackageCountsByVersion->get($version)),
            ));
        }

        return $packageVersionCounts;
    }
}
