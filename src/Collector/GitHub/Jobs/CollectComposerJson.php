<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\GitHub\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;
use JsonException;
use Maarheeze\Compass\Collector\Composer\Json\ComposerJsonReader;
use Maarheeze\Compass\Collector\Composer\Json\ComposerJsonSynchronizer;
use Maarheeze\Compass\Collector\GitHub\BlobQuery;
use Maarheeze\Compass\Collector\GitHub\GitHubQueryFailed;
use Maarheeze\Compass\Models\Repository;
use Throwable;

class CollectComposerJson implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly Repository $repository,
    ) {
    }

    /**
     * @throws GitHubQueryFailed
     * @throws JsonException
     * @throws Throwable
     */
    public function handle(
        BlobQuery $blobQuery,
        ComposerJsonReader $composerJsonReader,
        ComposerJsonSynchronizer $composerJsonSynchronizer,
    ): void {
        $oid = $this->repository->composer_json_oid;

        if ($oid === null) {
            $composerJsonSynchronizer->delete($this->repository);

            return;
        }

        $composerJsonSummary = $composerJsonReader->read($blobQuery->text($this->repository->name, $oid));
        $composerJsonSynchronizer->store($this->repository, $oid, $composerJsonSummary);
    }
}
