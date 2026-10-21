<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Composer\Lock;

use Illuminate\Database\ConnectionInterface;
use Maarheeze\Compass\Models\ComposerLock;
use Maarheeze\Compass\Models\Repository;
use Throwable;

readonly class ComposerLockSynchronizer
{
    public function __construct(
        private ConnectionInterface $connection,
    ) {
    }

    public function delete(Repository $repository): void
    {
        ComposerLock::query()
            ->where('repository_id', $repository->id)
            ->delete();
    }

    /**
     * @throws Throwable
     */
    public function store(Repository $repository, string $oid, ComposerLockSummary $composerLockSummary): void
    {
        $this->connection->transaction(static function () use ($repository, $oid, $composerLockSummary): void {
            $composerLock = ComposerLock::query()->updateOrCreate(['repository_id' => $repository->id], [
                'oid' => $oid,
                'php_constraint' => $composerLockSummary->phpConstraint,
            ]);

            $composerLock->packages()->delete();

            foreach ($composerLockSummary->lockedPackages as $lockedPackage) {
                $composerLock->packages()->create([
                    'name' => $lockedPackage->name,
                    'version' => $lockedPackage->version,
                    'section' => $lockedPackage->section,
                ]);
            }
        });
    }
}
