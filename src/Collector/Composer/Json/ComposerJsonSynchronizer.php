<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Composer\Json;

use Illuminate\Database\ConnectionInterface;
use Maarheeze\Compass\Models\ComposerJson;
use Maarheeze\Compass\Models\Repository;
use Throwable;

readonly class ComposerJsonSynchronizer
{
    public function __construct(
        private ConnectionInterface $connection,
    ) {
    }

    public function delete(Repository $repository): void
    {
        $repository->composerJson()->delete();
    }

    /**
     * @throws Throwable
     */
    public function store(Repository $repository, string $version, ComposerJsonSummary $composerJsonSummary): void
    {
        $this->connection->transaction(static function () use ($repository, $version, $composerJsonSummary): void {
            $composerJson = ComposerJson::query()->updateOrCreate(['repository_id' => $repository->id], [
                'version' => $version,
                'type' => $composerJsonSummary->type,
                'php_constraint' => $composerJsonSummary->phpConstraint,
                'minimum_stability' => $composerJsonSummary->minimumStability,
                'prefer_stable' => $composerJsonSummary->preferStable,
            ]);

            $composerJson->packages()->delete();

            foreach ($composerJsonSummary->requiredPackages as $requiredPackage) {
                $composerJson->packages()->create([
                    'name' => $requiredPackage->name,
                    'constraint' => $requiredPackage->constraint,
                    'section' => $requiredPackage->section,
                ]);
            }
        });
    }
}
