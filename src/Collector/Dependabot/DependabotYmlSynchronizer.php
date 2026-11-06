<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Dependabot;

use Illuminate\Support\Collection;
use Maarheeze\Compass\Models\DependabotYml;
use Maarheeze\Compass\Models\Repository;

readonly class DependabotYmlSynchronizer
{
    public function delete(Repository $repository): void
    {
        $repository->dependabotYml()->delete();
    }

    /**
     * @param Collection<int, string> $packageEcosystems
     */
    public function store(Repository $repository, string $version, Collection $packageEcosystems): void
    {
        DependabotYml::query()->updateOrCreate(['repository_id' => $repository->id], [
            'version' => $version,
            'package_ecosystems' => $packageEcosystems,
        ]);
    }
}
