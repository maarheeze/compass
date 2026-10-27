<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\GitHub\Jobs;

use Illuminate\Bus\Batch;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Maarheeze\Compass\Collector\CollectorLogger;
use Maarheeze\Compass\Collector\RunId;
use Maarheeze\Compass\Models\Repository;
use Webmozart\Assert\Assert;

class CollectChangedComposerJsons implements ShouldQueue
{
    use Batchable;
    use InteractsWithQueue;
    use Queueable;

    public function __construct(
        private readonly RunId $runId,
    ) {
    }

    public function handle(CollectorLogger $collectorLogger): void
    {
        $repositoriesWithChangedComposerJson = Repository::query()
            ->where('run_id', $this->runId)
            ->whereNotNull('composer_json_oid')
            ->whereDoesntHave('composerJson', static function (Builder $composerJsonQuery): void {
                $composerJsonQuery->whereColumn('oid', 'compass_repositories.composer_json_oid');
            })
            ->get();

        $repositoriesWithRemovedComposerJson = Repository::query()
            ->where('run_id', $this->runId)
            ->whereNull('composer_json_oid')
            ->has('composerJson')
            ->get();

        $collectComposerJsonJobs = [];

        foreach ($repositoriesWithChangedComposerJson->merge($repositoriesWithRemovedComposerJson) as $repository) {
            $collectComposerJsonJobs[] = new CollectComposerJson($repository);

            $collectorLogger->debug('Composer json collection queued', ['repository' => $repository->name]);
        }

        Assert::isInstanceOf($this->batch(), Batch::class)->add($collectComposerJsonJobs);
    }
}
