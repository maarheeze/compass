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
        ComposerJson::query()
            ->where('repository_id', $repository->id)
            ->delete();
    }

    /**
     * @throws Throwable
     */
    public function store(Repository $repository, string $oid, ComposerJsonSummary $composerJsonSummary): void
    {
        $this->connection->transaction(static function () use ($repository, $oid, $composerJsonSummary): void {
            $composerJson = ComposerJson::query()->updateOrCreate(['repository_id' => $repository->id], [
                'oid' => $oid,
                'type' => $composerJsonSummary->type,
                'php_constraint' => $composerJsonSummary->phpConstraint,
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
