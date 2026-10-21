<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Composer\Json;

use JsonException;
use Maarheeze\Compass\Collections\RequiredPackageCollection;
use Maarheeze\Compass\Collector\Composer\PackageSection;
use Webmozart\Assert\Assert;

use function array_key_exists;
use function json_decode;
use function str_contains;

use const JSON_THROW_ON_ERROR;

readonly class ComposerJsonReader
{
    /**
     * @throws JsonException
     */
    public function read(string $json): ComposerJsonSummary
    {
        $composerJson = Assert::isArray(json_decode($json, true, flags: JSON_THROW_ON_ERROR));
        $requirements = $this->getRequirements($composerJson, 'require');

        $productionPackages = $this->getRequiredPackages($requirements, PackageSection::PRODUCTION);
        $developmentPackages = $this->getRequiredPackages(
            $this->getRequirements($composerJson, 'require-dev'),
            PackageSection::DEVELOPMENT,
        );

        return new ComposerJsonSummary(
            $this->getType($composerJson),
            $this->getPhpConstraint($requirements),
            $productionPackages->merge($developmentPackages),
        );
    }

    /**
     * @param array<string, mixed> $requirements
     */
    private function getPhpConstraint(array $requirements): ?string
    {
        if (!array_key_exists('php', $requirements)) {
            return null;
        }

        return Assert::string($requirements['php']);
    }

    /**
     * @param array<string, mixed> $requirements
     */
    private function getRequiredPackages(array $requirements, PackageSection $section): RequiredPackageCollection
    {
        $requiredPackages = new RequiredPackageCollection();

        foreach ($requirements as $name => $constraint) {
            if (!str_contains($name, '/')) {
                continue;
            }

            $requiredPackages->push(new RequiredPackage($name, Assert::string($constraint), $section));
        }

        return $requiredPackages;
    }

    /**
     * @param array<mixed> $composerJson
     *
     * @return array<string, mixed>
     */
    private function getRequirements(array $composerJson, string $section): array
    {
        if (!array_key_exists($section, $composerJson)) {
            return [];
        }

        return Assert::isMap($composerJson[$section]);
    }

    /**
     * @param array<mixed> $composerJson
     */
    private function getType(array $composerJson): ?string
    {
        if (!array_key_exists('type', $composerJson)) {
            return null;
        }

        return Assert::string($composerJson['type']);
    }
}
