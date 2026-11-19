<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Composer\Lock;

use JsonException;
use Maarheeze\Compass\Collections\ComposerLockPackageCollection;
use Maarheeze\Compass\Collector\Composer\PackageSection;
use Maarheeze\Compass\Models\ComposerLockPackage;
use Webmozart\Assert\Assert;

use function array_key_exists;
use function json_decode;

use const JSON_THROW_ON_ERROR;

readonly class ComposerLockReader
{
    /**
     * @throws JsonException
     */
    public function read(string $json): ComposerLockSummary
    {
        $composerLock = Assert::isArray(json_decode($json, true, flags: JSON_THROW_ON_ERROR));

        $productionPackages = $this->getLockedPackages(
            Assert::isList($composerLock['packages']),
            PackageSection::PRODUCTION,
        );
        $developmentPackages = $this->getLockedPackages(
            Assert::isList($composerLock['packages-dev']),
            PackageSection::DEVELOPMENT,
        );

        return new ComposerLockSummary(
            $this->getPhpConstraint(Assert::isMap($composerLock['platform'])),
            $productionPackages->concat($developmentPackages),
        );
    }

    /**
     * @param list<mixed> $packageResponses
     */
    private function getLockedPackages(array $packageResponses, PackageSection $section): ComposerLockPackageCollection
    {
        $lockedPackages = new ComposerLockPackageCollection();

        foreach ($packageResponses as $packageResponse) {
            Assert::isArray($packageResponse);

            $lockedPackages->push(new ComposerLockPackage([
                'name' => Assert::string($packageResponse['name']),
                'version' => Assert::string($packageResponse['version']),
                'section' => $section,
            ]));
        }

        return $lockedPackages;
    }

    /**
     * @param array<string, mixed> $platform
     */
    private function getPhpConstraint(array $platform): ?string
    {
        if (!array_key_exists('php', $platform)) {
            return null;
        }

        return Assert::string($platform['php']);
    }
}
