<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Analyzer\Composer;

use Maarheeze\Compass\Models\Repository;

readonly class MinimumStabilityChecker
{
    public function isStable(Repository $repository): bool
    {
        if ($repository->composer_json_version === null) {
            return true;
        }

        $composerJson = $repository->composerJson;

        if ($composerJson === null || $composerJson->prefer_stable) {
            return true;
        }

        return $composerJson->minimum_stability === null || $composerJson->minimum_stability === 'stable';
    }
}
